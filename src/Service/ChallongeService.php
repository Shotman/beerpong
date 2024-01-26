<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\Tournament;
use App\Entity\TournamentResults;
use App\Repository\PlayerRepository;
use App\Repository\TournamentRepository;
use App\Structs\Team;
use DateTime;
use Exception;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChallongeService
{
    private array $participants;
    private int $loopIndex = 0;
    private string $baseUrl;
    private bool $saveTournament = false;
    private stdClass $tournament;
    private array $pointMatrix;
    private array $formattedParticipants;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ParameterBagInterface $parameterBag,
        private readonly TournamentRepository $tournamentRepository,
        private readonly PlayerRepository $playerRepository,
        private CacheInterface $challongeCache)
    {
        $this->participants = [];
        $this->formattedParticipants = [];
        $this->baseUrl = "https://api.challonge.com/v1";
        $this->pointMatrix = [];
    }

    public function saveTournament(): self
    {
        $this->saveTournament = true;
        return $this;
    }

    private function getTournament($tournament, $refresh): void
    {
        if ($refresh) {
            $this->participants = [];
            $this->formattedParticipants = [];
            $this->pointMatrix = [];
            $this->loopIndex = 0;
        }
        $beta = match ($refresh) {
            true => INF,
            false => 0
        };
        $tournamentResponse = $this->challongeCache->get('challonge' . $tournament, function ($item) use ($tournament) {
            $item->expiresAfter(3600);
            return $this->getTournamentResponse($tournament);
        }, $beta);
        $this->tournament = json_decode($tournamentResponse);

        $this->participants = array_column($this->tournament->tournament->participants, "participant");
        $ranks = array_values(array_unique(array_column($this->participants, "final_rank")));
        rsort($ranks);
        foreach ($ranks as $rank) {
            $this->generatePointMatrix($rank);
        }
    }

    /**
     * @param Tournament|null $tournament
     * @param Team[] $teams
     * @return mixed
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createTournament(?Tournament $tournament, array $teams)
    {
        if (is_null($tournament)) {
            $tournament = new Tournament();
            $tournament->setName("Tournois du " . date("d/m/Y"));
            $tournament->setDate(new DateTime());
            $this->tournamentRepository->save($tournament);
        }
        if (is_null($tournament->getChallongeId())) {
            $createTournamentResponse = $this->client->request("POST", $this->baseUrl . "/tournaments.json", ["query" => ["api_key" => $this->parameterBag->get("challonge_api_key"), "tournament" => ["name" => $tournament->getName(), "open_signup" => false, "private" => true, "game_name" => $tournament->getExtraData()["game"] ?? "BeerPong",]]]);
            $tournamentChallonge = json_decode($createTournamentResponse->getContent())->tournament;
            $tournament->setChallongeId($tournamentChallonge->url);
            $this->tournamentRepository->save($tournament);
        }
        $this->addParticipantsToTournament($tournament, $teams);
    }

    private function getResults(): array
    {
        foreach ($this->participants as $participant) {
            $partners = explode("&", $participant->name);
            foreach ($partners as $partner) {
                $this->formattedParticipants[] = ["name" => trim($partner), "points" => $this->pointMatrix[$participant->final_rank], "rank" => $participant->final_rank];
            }
        }
        usort($this->formattedParticipants, function ($a, $b) {
            return $a["rank"] <=> $b["rank"];
        });
        return $this->formattedParticipants;
    }

    /**
     * @throws Exception
     */
    public function getTournamentDetails($tournament, $refresh = false): array
    {
        /**
         * @var Tournament $tournamentDb
         */
        $tournamentDb = $this->tournamentRepository->findOneBy(["challongeId" => $tournament]);
        if (!is_null($tournamentDb) && $tournamentDb->getTournamentResults()->count() > 0) {
            return ["id" => $tournamentDb->getChallongeId(), "name" => $tournamentDb->getName(), "date" => $tournamentDb->getDate(), "number_of_teams" => $tournamentDb->getExtraData()["number_of_teams"], "participants" => $tournamentDb->getTournamentResults()->map(function (
                $tournamentResult) {
                return ["name" => $tournamentResult->getPlayer()->getName(), "points" => $tournamentResult->getPoints(), "rank" => $tournamentResult->getRank()];
            })->toArray()];
        }
        $this->getTournament($tournament, $refresh);
        $tournamentDetails = ["raw" => $this->tournament->tournament, "id" => $this->tournament->tournament->url, "name" => $this->tournament->tournament->name, "date" => new DateTime($this->tournament->tournament->started_at), "number_of_teams" => $this->tournament->tournament->participants_count, "participants" => $this->getResults()];
        if ($this->saveTournament && $this->tournament->tournament->state == "complete") {
            $this->saveTournamentData($tournamentDetails);
        }
        return $tournamentDetails;
    }

    public function terminateTournament(Tournament $tournament)
    {
        $query = ["api_key" => $this->parameterBag->get("challonge_api_key"),];
        $this->client->request("POST", $this->baseUrl . "/tournaments/" . $tournament->getChallongeId() . "/finalize.json", ["query" => $query,]);
    }

    protected function generatePointMatrix($rank): void
    {
        $this->pointMatrix[$rank] = pow(2, $this->loopIndex);
        $this->loopIndex++;
    }

    /**
     * @param $tournamentChallonge
     * @param Team[] $teams
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function addParticipantsToTournament($tournamentChallonge, array $teams): void
    {
        $participants = [];
        foreach ($teams as $team) {
            $participants[] = ["name" => $team->getTeamName()];
        }
        $participants = json_encode(["participants" => $participants]);
        $query = ["api_key" => $this->parameterBag->get("challonge_api_key"),];
        try {
            $this->client->request("POST", $this->baseUrl . "/tournaments/" . $tournamentChallonge->getChallongeId() . "/participants/bulk_add.json", ["query" => $query, "body" => $participants, "headers" => ["Content-Type" => "application/json"]]);
            $this->randomizeTournament($tournamentChallonge);
        } catch (Exception $exception) {
            dd($exception);
        }
    }

    private function saveTournamentData(array $tournamentDetails)
    {
        $tournament = $this->tournamentRepository->findOneBy(["challongeId" => $this->tournament->tournament->url]) ?: new Tournament();
        $slugger = new AsciiSlugger();
        if ($tournament->getName() == null) {
            $tournament->setName($tournamentDetails["name"]);
            $tournament->setDate($tournamentDetails["date"]);
            $tournament->setChallongeId($this->tournament->tournament->url);
            $tournament->setExtraData(["number_of_teams" => $tournamentDetails["number_of_teams"]]);
        }
        foreach ($tournamentDetails["participants"] as $participant) {
            $player = $this->playerRepository->findOneBy(["identifier" => strtolower($slugger->slug($participant["name"]))]) ?: new Player();
            if ($player->getName() == null) {
                $player->setName($participant["name"]);
                $this->playerRepository->save($player);
            }
            $tournamentResult = new TournamentResults();
            $tournamentResult->setPlayer($player);
            $tournamentResult->setPoints($participant["points"]);
            $tournamentResult->setRank($participant["rank"]);
            $tournament->addTournamentResult($tournamentResult);
        }
        $this->tournamentRepository->save($tournament);
    }

    private function getTournamentResponse($tournament): mixed
    {
        $tournamentResponse = $this->client->request("GET", $this->baseUrl . "/tournaments/" . $tournament . ".json", ["query" => ["api_key" => $this->parameterBag->get("challonge_api_key"), "include_participants" => 1, "include_matches" => 1,]]);
        return $tournamentResponse->getContent();
    }

    private function randomizeTournament($tournamentChallonge)
    {
        $query = ["api_key" => $this->parameterBag->get("challonge_api_key"),];
        $this->client->request("POST", $this->baseUrl . "/tournaments/" . $tournamentChallonge->getChallongeId() . "/participants/randomize.json", ["query" => $query,]);
    }

    public function startTournament($tournamentChallonge): mixed
    {
        $query = ["api_key" => $this->parameterBag->get("challonge_api_key"), "include_matches" => 1];
        return $this->client->request("POST", $this->baseUrl . "/tournaments/" . $tournamentChallonge->getChallongeId() . "/start.json", ["query" => $query,])->getContent();
    }

    public function deleteTournament(Tournament $tournament): void
    {
        $query = ["api_key" => $this->parameterBag->get("challonge_api_key"),];
        if ($tournament->getChallongeId() == null) {
            return;
        }
        $this->client->request("DELETE", $this->baseUrl . "/tournaments/" . $tournament->getChallongeId() . ".json", ["query" => $query,]);
    }

    public function getTournamentMatches(Tournament $tournament)
    {
        $query = ["api_key" => $this->parameterBag->get("challonge_api_key"),];
        if($tournament->getChallongeId() == null){
            return [];
        }
        $matchesResponse = $this->client->request("GET", $this->baseUrl . "/tournaments/" . $tournament->getChallongeId() . "/matches.json", ["query" => $query,]);
        return array_filter(array_map(function($match){
            return $match->match->state === "open" ? $match->match : false;
        }, json_decode($matchesResponse->getContent())));
    }

    public function getParticipantsDetails(Tournament $tournament, bool $refresh = false)
    {
        $beta = match ($refresh) {
            true => INF,
            false => 0
        };
        $participants = $this->challongeCache->get('challonge' . $tournament->getChallongeId() . 'participants', function (
            $item) use ($tournament) {
            $item->expiresAfter(3600);
            $query = ["api_key" => $this->parameterBag->get("challonge_api_key"),];
            if ($tournament->getChallongeId() == null) {
                return [];
            }
            $particpantsResponse = $this->client->request("GET", $this->baseUrl . "/tournaments/" . $tournament->getChallongeId() . "/participants.json", ["query" => $query,]);
            $participants = array_map(function ($participant) {
                return $participant->participant;
            }, json_decode($particpantsResponse->getContent()));
            $participants = array_combine(array_column($participants, "id"), $participants);
            return $participants;
        }, $beta);
        return $participants;
    }
}
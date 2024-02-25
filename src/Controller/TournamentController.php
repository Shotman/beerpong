<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Form\ImportTournamentType;
use App\Form\TeamTournamentType;
use App\Form\TournamentType;
use App\Repository\PlayerRepository;
use App\Repository\TournamentRepository;
use App\Service\ChallongeService;
use App\Structs\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[Route(path: [
    "fr" => "/tournois",
    "en" => "/tournaments",
])]
class TournamentController extends AbstractBeerpongController
{
    #[Route('/', name: 'app_tournament_index', methods: ['GET'])]
    public function index(TournamentRepository $tournamentRepository): Response
    {
        return $this->render('tournament/index.html.twig', [
            'tournaments' => $tournamentRepository->findAll(),
        ]);
    }

    #[Route(path: [
        "fr" => "/nouveau",
        "en" => "/new",
    ], name: 'app_tournament_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if(!$this->isGranted("ROLE_ADMIN")){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tournament->setExtraData([
                'game' => $request->get('tournament')['gameName']
            ]);
            if($request->get("tournament")["championship"]){
                $tournament->setPublic($request->get("tournament")["championship"]->isPublic());
            }
            $tournament->setAdmin($this->getUser());
            $entityManager->persist($tournament);
            $entityManager->flush();

            return $this->redirectToRoute('app_tournament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournament/new.html.twig', [
            'tournament' => $tournament,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tournament_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Tournament $tournament, ChallongeService $challongeService): Response
    {
        $flashMessage = "Vous n'avez pas les droits pour effectuer cette action";
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $tournament->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$tournament->isPublic() && $rightAdminOrSuperAdmin){
            $this->addFlash('error', $flashMessage);
            return $this->redirectToRoute('app_tournament_index');
        }
        $matches = $this->getTournamentMatches($tournament, $challongeService);
        $participants = $this->getTournamentParticipantsDetails($tournament, $challongeService);
        return $this->render('tournament/show.html.twig', [
            'tournament' => $tournament,
            'matches' => $matches,
            'participants' => $participants,
        ]);
    }

    #[Route('/{id}/matches', name: 'app_tournament_matches', methods: ['GET'])]
    public function matches(Tournament $id, ChallongeService $challongeService): Response
    {
        $participants = $this->getTournamentParticipantsDetails($id, $challongeService);
        $matches = $this->getTournamentMatches($id, $challongeService);
        return $this->render('tournament/_partial/matches.html.twig', [
            'tournament' => $id,
            'participants' => $participants,
            'matches' => $matches,
        ]);
    }

    #[Route(path: '/{tournament}/winner', name: 'app_tournament_match_update', methods: ['POST'], schemes: ["https"])]
    public function updateMatch(Request $request, Tournament $tournament, ChallongeService $challongeService): Response
    {
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $tournament->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        $challongeService->setMatchWinner($tournament, $request);
        $matches = $this->getTournamentMatches($tournament, $challongeService);
        $participants = $this->getTournamentParticipantsDetails($tournament, $challongeService);
        return $this->renderBlock('tournament/show.html.twig',"body", [
            'tournament' => $tournament,
            'participants' => $participants,
            'matches' => $matches,
        ]);
    }

    #[Route(path: [
        "fr" => "/{id}/modifier",
        "en" => "/{id}/edit",
    ], name: 'app_tournament_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $tournament->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tournament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournament/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_tournament_delete', requirements: ['id' => '\d+'] ,methods: ['DELETE'])]
    public function delete(Request $request, Tournament $id, EntityManagerInterface $entityManager, ChallongeService $challongeService): Response
    {
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $id->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        if ($this->isCsrfTokenValid('delete'.$id->getId(), $request->headers->get('x-csrftoken'))) {
            $challongeService->deleteTournament($id);
            $entityManager->remove($id);
            $entityManager->flush();
            return new JsonResponse('', Response::HTTP_OK,[
                "HX-Redirect" => $this->generateUrl('app_tournament_index')
            ]);
        }
        return new JsonResponse('', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Route(path: [
        "fr" => "/{id}/démarrer",
        "en" => "/{id}/init",
    ], name: 'app_tournament_init', methods: ['GET', 'POST'])]
    public function init(
        Request $request,
        Tournament $tournament,
        TournamentRepository $tournamentRepository,
        ChallongeService $challongeService,
        PlayerRepository $playerRepository,
        CacheInterface $randomCache,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response
    {
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $tournament->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        $data = $randomCache->getItem('tournament_'.$tournament->getId()."_teams")->get() ?? null;
        $form = $this->createForm(TeamTournamentType::class, $data, [
            "em" => $em,
            "tournament" => $tournament,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted()){
            if(!array_key_exists("createTournamentResponse",$tournament->getExtraData())) {
                $teamNamesAssoc = [];
                $teams = [];
                foreach ($request->get('team_tournament')['teams'] as $team) {
                    $player1Slug = $slugger->slug($team["player1"]);
                    $player2Slug = $slugger->slug($team["player2"]);
                    $player1 = $playerRepository->findOneBy(['identifier' => $player1Slug]);
                    $player2 = $playerRepository->findOneBy(['identifier' => $player2Slug]);
                    $tmpTeam = new Team($player1, $player2, $playerRepository);
                    if ($team["teamName"]) {
                        $tmpTeam->setTeamName($team["teamName"]);
                    }
                    $teamNamesAssoc[$tmpTeam->getTeamName()] = $tmpTeam->getPlayerTeamName();
                    $teams[] = $tmpTeam;
                }
                $teamsCache = $randomCache->getItem('tournament_' . $tournament->getId() . "_teamsNames");
                $teamsCache->set($teamNamesAssoc);
                $randomCache->save($teamsCache);
                $createTournamentResponse = $challongeService->createTournament($tournament, $teams);
                if ($createTournamentResponse instanceof \Exception) {
                    $this->addFlash('error', $createTournamentResponse->getMessage());
                    return $this->redirectToRoute('app_tournament_show', ['id' => $tournament->getId()]);
                }
                $tournament->setExtraData(["createTournamentResponse" => $createTournamentResponse]);
                $tournamentRepository->save($tournament);
            }
            $randomizeTournamentResponse = $challongeService->randomizeTournament($tournament);
            if($randomizeTournamentResponse instanceof \Exception){
                $this->addFlash('error', $randomizeTournamentResponse->getMessage());
                return $this->redirectToRoute('app_tournament_show', ['id' => $tournament->getId()]);
            }
            $startTournamentResponse = $challongeService->startTournament($tournament);
            if($startTournamentResponse instanceof \Exception){
                $this->addFlash('error', $startTournamentResponse->getMessage());
                return $this->redirectToRoute('app_tournament_show', ['id' => $tournament->getId()]);
            }
            return $this->redirectToRoute('app_tournament_show', ['id' => $tournament->getId()]);
        }

        return $this->render('tournament/init.html.twig', [
            'tournament' => $tournament,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tournois/{tournament}/finish', name: 'app_tournament_finish', methods: ['POST'])]
    public function finish(Request $request, Tournament $tournament, ChallongeService $challongeService, CacheInterface $randomCache): Response
    {
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $tournament->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        $cacheItem = $randomCache->getItem('tournament_'.$tournament->getId()."_teamsNames");
        $challongeService->finalizeTournament($tournament, $cacheItem->get());
        return new JsonResponse('', Response::HTTP_OK,[
            "HX-Redirect" => $this->generateUrl('app_tournament_show', ['id' => $tournament->getId()])
        ]);
    }

    #[Route(path: [
        "fr" => "/{id}/sauvegarder",
        "en" => "/{id}/save",
    ], name: 'app_tournament_save', methods: ['POST'])]
    public function save(Request $request, Tournament $tournament ,CacheInterface $randomCache, EntityManagerInterface $em): Response
    {
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $tournament->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        $form = $this->createForm(TeamTournamentType::class, null, [
            "em" => $em,
            "tournament" => $tournament,
        ]);
        $form->handleRequest($request);
        $cacheItem = $randomCache->getItem('tournament_'.$tournament->getId()."_teams");
        $cacheItem->set($form->getData());
        $randomCache->save($cacheItem);
        return $this->renderBlock('global/partials.html.twig', "successAlert", [
            'message' => "Les équipes ont bien été sauvegardées",
        ]);
    }

    private function getTournamentMatches(Tournament $tournament, ChallongeService $challongeService): array
    {
        return $challongeService->getTournamentMatches($tournament);
    }

    private function getTournamentParticipantsDetails(Tournament $tournament, ChallongeService $challongeService): array
    {
        return $challongeService->getParticipantsDetails($tournament,true);
    }
}

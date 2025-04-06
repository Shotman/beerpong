<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Form\TeamTournamentType;
use App\Form\TournamentType;
use App\Repository\ChampionshipRepository;
use App\Repository\PlayerRepository;
use App\Repository\TournamentRepository;
use App\Service\ChallongeService;
use App\Service\WebPushService;
use App\Structs\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[
    Route(
        path: [
            "fr" => "/tournois",
            "en" => "/tournaments",
        ]
    )
]
class TournamentController extends BaseController
{
    #[Route("/", name: "app_tournament_index", methods: ["GET"])]
    public function index(
        TournamentRepository $tournamentRepository,
        Security $security,
        RequestStack $requestStack,
    ): Response {
        $seeAll = $this->isGranted("LIST_ALL_CHAMPIONSHIP_TOURNAMENT");
        $user = $security->getUser();
        return $this->render("tournament/index.html.twig", [
            "tournaments" => $tournamentRepository->findAllFiltered(
                $user,
                $seeAll
            ),
        ]);
    }

    #[
        Route(
            path: [
                "fr" => "/nouveau",
                "en" => "/new",
            ],
            name: "app_tournament_new",
            methods: ["GET", "POST"]
        )
    ]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ChampionshipRepository $championshipRepository
    ): Response {
        if (!$this->isGranted("CREATE_CHAMPIONSHIP_TOURNAMENT")) {
            $this->addFlash(
                "error",
                "Vous n'avez pas les droits pour effectuer cette action"
            );
            return $this->redirectToRoute("app_tournament_index");
        }
        $tournament = new Tournament();
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tournament->setExtraData([
                "game" => $request->get("tournament")["gameName"],
            ]);
            if ($request->get("tournament")["championship"]) {
                $championship = $championshipRepository->find(
                    (int) $request->get("tournament")["championship"]
                );
                $tournament->setPublic($championship->isPublic());
            }
            $tournament->setAdmin($this->getUser());
            $entityManager->persist($tournament);
            $entityManager->flush();

            return $this->redirectToRoute(
                "app_tournament_index",
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render("tournament/new.html.twig", [
            "tournament" => $tournament,
            "form" => $form->createView(),
        ]);
    }

    #[
        Route(
            "/{id}",
            name: "app_tournament_show",
            requirements: ["id" => "\d+"],
            methods: ["GET"]
        )
    ]
    public function show(
        Tournament $tournament,
        ChallongeService $challongeService
    ): Response {
        $matches = null;
        $flashMessage =
            "Vous n'avez pas les droits pour effectuer cette action";
        $rightAdminOrSuperAdmin =
            !is_null($this->getUser()) &&
            $this->getUser()->getUserIdentifier() !==
                $tournament->getAdmin()->getUserIdentifier() &&
            !$this->isGranted("ROLE_SUPER_ADMIN");
        if (!$tournament->isPublic() && $rightAdminOrSuperAdmin) {
            $this->addFlash("error", $flashMessage);
            return $this->redirectToRoute("app_tournament_index");
        }
        if($this->isGranted("EDIT_CHAMPIONSHIP_TOURNAMENT", $tournament)){
            $matches = $this->getTournamentMatches($tournament, $challongeService);
        }
        $participants = $this->getTournamentParticipantsDetails(
            $tournament,
            $challongeService
        );
        return $this->render("tournament/show.html.twig", [
            "tournament" => $tournament,
            "matches" => $matches,
            "participants" => $participants,
        ]);
    }

    #[Route("/{id}/matches", name: "app_tournament_matches", methods: ["GET"])]
    public function matches(
        Tournament $id,
        ChallongeService $challongeService
    ): Response {
        if(!$this->isGranted("EDIT_CHAMPIONSHIP_TOURNAMENT", $id)){
            $this->addFlash("error", "Vous n'avez pas les droits pour effectuer cette action");
            return new Response("", Response::HTTP_FORBIDDEN,[
                "HX-Refresh" => true,
            ]);
        }
        $participants = $this->getTournamentParticipantsDetails(
            $id,
            $challongeService
        );
        $matches = $this->getTournamentMatches($id, $challongeService);
        return $this->render("tournament/_partial/matches.html.twig", [
            "tournament" => $id,
            "participants" => $participants,
            "matches" => $matches,
        ]);
    }

    #[
        Route(
            path: "/{id}/winner",
            name: "app_tournament_match_update",
            methods: ["POST"],
        )
    ]
    public function updateMatch(
        Tournament $id,
        Request $request,
        ChallongeService $challongeService
    ): Response {
//        if (!$this->isGranted("EDIT_CHAMPIONSHIP_TOURNAMENT")) {
//            return new JsonResponse(["message" => "Vous n'avez pas les droits pour edit cette action"], Response::HTTP_FORBIDDEN);
//        }
        $challongeService->setMatchWinner($id, $request);
        $matches = $this->getTournamentMatches($id, $challongeService);
        $participants = $this->getTournamentParticipantsDetails(
            $id,
            $challongeService
        );
        $response = null;
        if(count($matches) === 0){
            $response = new Response("", Response::HTTP_OK,[
                "HX-Refresh" => true,
            ]);
        }
        return $this->render("tournament/_partial/matches.html.twig", [
            "tournament" => $id,
            "participants" => $participants,
            "matches" => $matches,
        ],$response);
    }

    #[
        Route(
            path: [
                "fr" => "/{id}/modifier",
                "en" => "/{id}/edit",
            ],
            name: "app_tournament_edit",
            methods: ["GET", "POST"]
        )
    ]
    public function edit(
        Request $request,
        Tournament $tournament,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isGranted("EDIT_CHAMPIONSHIP_TOURNAMENT", $tournament)) {
            $this->addFlash(
                "error",
                "Vous n'avez pas les droits pour effectuer cette action"
            );
            return $this->redirectToRoute("app_tournament_index");
        }
        $form = $this->createForm(TournamentType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute(
                "app_tournament_index",
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render("tournament/edit.html.twig", [
            "tournament" => $tournament,
            "form" => $form,
        ]);
    }

    #[
        Route(
            [
                "fr" => "/{id}/supprimer",
                "en" => "/{id}/delete",
            ],
            name: "app_tournament_delete",
            requirements: ["id" => "\d+"],
            methods: ["DELETE"]
        )
    ]
    public function delete(
        Request $request,
        Tournament $id,
        EntityManagerInterface $entityManager,
        ChallongeService $challongeService
    ): Response {
        if (!$this->isGranted("DELETE_CHAMPIONSHIP_TOURNAMENT", $id)) {
            $this->addFlash(
                "error",
                "Vous n'avez pas les droits pour effectuer cette action"
            );
            return new Response("", Response::HTTP_FORBIDDEN, [
                "HX-Refresh" => true,
            ]);
        }
        if (
            $this->isCsrfTokenValid(
                "delete" . $id->getId(),
                $request->headers->get("x-csrftoken")
            )
        ) {
            $challongeService->deleteTournament($id);
            $entityManager->remove($id);
            $entityManager->flush();
            return new JsonResponse("", Response::HTTP_OK, [
                "HX-Redirect" => $this->generateUrl("app_tournament_index"),
            ]);
        }
        return new JsonResponse("", Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[
        Route(
            path: [
                "fr" => "/{id}/démarrer",
                "en" => "/{id}/init",
            ],
            name: "app_tournament_init",
            methods: ["GET", "POST"]
        )
    ]
    public function init(
        Request $request,
        Tournament $tournament,
        TournamentRepository $tournamentRepository,
        ChallongeService $challongeService,
        PlayerRepository $playerRepository,
        CacheInterface $randomCache,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $rightAdminOrSuperAdmin =
            !is_null($this->getUser()) &&
            $this->getUser()->getUserIdentifier() !==
                $tournament->getAdmin()->getUserIdentifier() &&
            !$this->isGranted("ROLE_SUPER_ADMIN");
        if (!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin) {
            $this->addFlash(
                "error",
                "Vous n'avez pas les droits pour effectuer cette action"
            );
            return $this->redirectToRoute("app_tournament_index");
        }

        $data =
            $randomCache
                ->getItem("tournament_" . $tournament->getId() . "_teams")
                ->get() ?? null;
        $form = $this->createForm(TeamTournamentType::class, $data, [
            "em" => $em,
            "tournament" => $tournament,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (
                !array_key_exists(
                    "createTournamentResponse",
                    $tournament->getExtraData()
                )
            ) {
                $teamNamesAssoc = [];
                $teams = [];
                foreach ($request->get("team_tournament")["teams"] as $team) {
                    $player1Slug = $slugger->slug($team["player1"]);
                    $player2Slug = $slugger->slug($team["player2"]);
                    $player1 = $playerRepository->findOneBy([
                        "identifier" => $player1Slug,
                    ]);
                    $player2 = $playerRepository->findOneBy([
                        "identifier" => $player2Slug,
                    ]);
                    $tmpTeam = new Team($player1, $player2, $playerRepository);
                    if ($team["teamName"]) {
                        $tmpTeam->setTeamName($team["teamName"]);
                    }
                    $teamNamesAssoc[
                        $tmpTeam->getTeamName()
                    ] = $tmpTeam->getPlayerTeamName();
                    $teams[] = $tmpTeam;
                }
                $teamsCache = $randomCache->getItem(
                    "tournament_" . $tournament->getId() . "_teamsNames"
                );
                $teamsCache->set($teamNamesAssoc);
                $randomCache->save($teamsCache);
                $createTournamentResponse = $challongeService->createTournament(
                    $tournament,
                    $teams
                );
                if ($createTournamentResponse instanceof \Exception) {
                    $this->addFlash(
                        "error",
                        $createTournamentResponse->getMessage()
                    );
                    return $this->redirectToRoute("app_tournament_show", [
                        "id" => $tournament->getId(),
                    ]);
                }
                $tournament->setExtraData([
                    "createTournamentResponse" => $createTournamentResponse,
                ]);
                $tournamentRepository->save($tournament);
            }
            // $randomizeTournamentResponse = $challongeService->randomizeTournament($tournament);
            // if($randomizeTournamentResponse instanceof \Exception){
            //     $this->addFlash('error', $randomizeTournamentResponse->getMessage());
            //     return $this->redirectToRoute('app_tournament_show', ['id' => $tournament->getId()]);
            // }
            $startTournamentResponse = $challongeService->startTournament(
                $tournament
            );
            if ($startTournamentResponse instanceof \Exception) {
                $this->addFlash(
                    "error",
                    $startTournamentResponse->getMessage()
                );
                return $this->redirectToRoute("app_tournament_show", [
                    "id" => $tournament->getId(),
                ]);
            }
            return $this->redirectToRoute("app_tournament_show", [
                "id" => $tournament->getId(),
            ]);
        }

        return $this->render("tournament/init.html.twig", [
            "tournament" => $tournament,
            "form" => $form->createView(),
        ]);
    }

    #[Route("/{tournament}/finish", name: "app_tournament_finish")]
    public function finish(
        Request $request,
        Tournament $tournament,
        ChallongeService $challongeService,
        CacheInterface $randomCache
    ): Response {
        $rightAdminOrSuperAdmin =
            !is_null($this->getUser()) &&
            $this->getUser()->getUserIdentifier() !==
                $tournament->getAdmin()->getUserIdentifier() &&
            !$this->isGranted("ROLE_SUPER_ADMIN");
        if (!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin) {
            $this->addFlash(
                "error",
                "Vous n'avez pas les droits pour effectuer cette action"
            );
            return $this->redirectToRoute("app_tournament_index");
        }
        $cacheItem = $randomCache->getItem(
            "tournament_" . $tournament->getId() . "_teamsNames"
        );
        $challongeService->finalizeTournament($tournament, $cacheItem->get());
        return new JsonResponse("", Response::HTTP_OK, [
            "HX-Redirect" => $this->generateUrl("app_tournament_show", [
                "id" => $tournament->getId(),
            ]),
        ]);
    }

    #[
        Route(
            path: [
                "fr" => "/{id}/sauvegarder",
                "en" => "/{id}/save",
            ],
            name: "app_tournament_save",
            methods: ["POST"]
        )
    ]
    public function save(
        Request $request,
        Tournament $tournament,
        CacheInterface $randomCache,
        EntityManagerInterface $em
    ): Response {
        $rightAdminOrSuperAdmin =
            !is_null($this->getUser()) &&
            $this->getUser()->getUserIdentifier() !==
                $tournament->getAdmin()->getUserIdentifier() &&
            !$this->isGranted("ROLE_SUPER_ADMIN");
        if (!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin) {
            $this->addFlash(
                "error",
                "Vous n'avez pas les droits pour effectuer cette action"
            );
            return $this->redirectToRoute("app_tournament_index");
        }
        $form = $this->createForm(TeamTournamentType::class, null, [
            "em" => $em,
            "tournament" => $tournament,
        ]);
        $form->handleRequest($request);
        $cacheItem = $randomCache->getItem(
            "tournament_" . $tournament->getId() . "_teams"
        );
        $cacheItem->set($form->getData());
        $randomCache->save($cacheItem);
        return $this->renderBlock("global/partials.html.twig", "successAlert", [
            "message" => "Les équipes ont bien été sauvegardées",
        ]);
    }

    #[Route('/registerWebPushSub', name: 'app_tournament_registerWebPushSub', methods: ['POST'])]
    function registerWebPushSub(RequestStack $requestStack, WebPushService $webPush): JsonResponse
    {
        $requestStack->getSession()->set("persist", true);
        $content = $requestStack->getCurrentRequest()->getContent();
        [$sub, $context] = array_values(json_decode($content, true));
        $webPush->registerSubscription(["context" => $context, "content" => json_encode($sub)], $requestStack->getSession()->getId());
        return new JsonResponse("OK");
    }
    #[Route('/unregisterWebPushSub', name: 'app_tournament_unregisterWebPushSub', methods: ['POST'])]
    function unregisterWebPushSub(RequestStack $requestStack, WebPushService $webPush): JsonResponse
    {
        $requestStack->getSession()->set("persist", false);
        $content = $requestStack->getCurrentRequest()->getContent();
        [$sub, $context] = array_values(json_decode($content, true));
        $webPush->unregisterSubscription(["context" => $context, "content" => json_encode($sub)], $requestStack->getSession()->getId());
        return new JsonResponse("OK");
    }

    #[Route('/sendWebPush', name: 'app_tournament_sendWebPush')]
    public function sendWebPush(RequestStack $request, WebPushService $webPush) {
        $data = $request->getCurrentRequest()->request->all();
        $webPush->sendPushNotification("DÉBUT DE MATCH",$data["team1"] . " VS " . $data["team2"],$data["tournament"]);
        return new JsonResponse("OK");
    }

    private function getTournamentMatches(
        Tournament $tournament,
        ChallongeService $challongeService
    ): array {
        return $challongeService->getTournamentMatches($tournament);
    }

    private function getTournamentParticipantsDetails(
        Tournament $tournament,
        ChallongeService $challongeService
    ): array {
        return $challongeService->getParticipantsDetails($tournament, true);
    }
}

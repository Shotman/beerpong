<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Player;
use App\Form\ChampionshipType;
use App\Repository\ChampionshipRepository;
use App\Repository\PlayerRepository;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route([
    "fr" => "/championnats",
    "en" => "/championships",
])]
class ChampionshipController extends AbstractBeerpongController
{
    #[Route('/', name: 'app_championship_index', methods: ['GET'])]
    public function index(ChampionshipRepository $championshipRepository): Response
    {
        return $this->render('championship/index.html.twig', [
            'championships' => $championshipRepository->findAll(),
        ]);
    }

    #[Route([
        "fr" => "/nouveau",
        "en" => "/new",
    ], name: 'app_championship_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if(!$this->isGranted("ROLE_ADMIN")){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_championship_index');
        }
        $championship = new Championship();
        $form = $this->createForm(ChampionshipType::class, $championship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($championship);
            $entityManager->flush();

            return $this->redirectToRoute('app_championship_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('championship/new.html.twig', [
            'championship' => $championship,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_championship_show', methods: ['GET'])]
    public function show(Championship $championship, PlayerRepository $playerRepository, TournamentRepository $tournamentRepository): Response
    {
        $allChampionshipPlayers = $playerRepository->getAllPlayerByChampionShip($championship);
        $allTournaments = $tournamentRepository->findBy(['championship' => $championship]);
        usort($allChampionshipPlayers,fn(Player $playerA,Player $playerB) => $playerB->getTotalPointsByChampionship($championship) <=> $playerA->getTotalPointsByChampionship($championship));
        return $this->render('championship/show.html.twig', [
            'championship' => $championship,
            'allChampionshipPlayers' => $allChampionshipPlayers,
            'allTournaments' => $allTournaments,
        ]);
    }

    #[Route([
        "fr" => "/{id}/modifier",
        "en" => "/{id}/edit",
    ], name: 'app_championship_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Championship $championship, EntityManagerInterface $entityManager): Response
    {
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $championship->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        $form = $this->createForm(ChampionshipType::class, $championship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_championship_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('championship/edit.html.twig', [
            'championship' => $championship,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_championship_delete', methods: ['DELETE'])]
    public function delete(Request $request, Championship $championship, EntityManagerInterface $entityManager): Response
    {
        $rightAdminOrSuperAdmin = !is_null($this->getUser()) && $this->getUser()->getUserIdentifier() !== $championship->getAdmin()->getUserIdentifier() && !$this->isGranted("ROLE_SUPER_ADMIN");
        if(!$this->isGranted("ROLE_ADMIN") && $rightAdminOrSuperAdmin){
            $this->addFlash('error', "Vous n'avez pas les droits pour effectuer cette action");
            return $this->redirectToRoute('app_tournament_index');
        }
        $entityManager->remove($championship);
        $entityManager->flush();
        return new JsonResponse('', Response::HTTP_OK,[
            "HX-Redirect" => $this->generateUrl('app_championship_index')
        ]);
    }
}

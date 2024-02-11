<?php

namespace App\Controller;

use App\Repository\ChampionshipRepository;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractBeerpongController
{
    #[Route('/', name: 'app_index')]
    public function index(ChampionshipRepository $championshipRepository, TournamentRepository $tournamentRepository): Response
    {
        $activeChampionships = $championshipRepository->getActiveChampionships();
        $commingTournaments = $tournamentRepository->getCommingTournaments();
        return $this->render('index/index.html.twig', [
            'activeChampionships' => $activeChampionships,
            'commingTournaments' => $commingTournaments,
            'controller_name' => 'IndexController',
        ]);
    }
}

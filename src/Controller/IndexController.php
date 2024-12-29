<?php

namespace App\Controller;

use App\Repository\ChampionshipRepository;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route("/", name: "app_index")]
    public function index(
        ChampionshipRepository $championshipRepository,
        TournamentRepository $tournamentRepository
    ): Response {
        $activeChampionships = array_filter(
            $championshipRepository->getActiveChampionships(),
            function ($championship) {
                return $this->isGranted(
                    "VIEW_CHAMPIONSHIP_TOURNAMENT",
                    $championship
                );
            }
        );

        $commingTournaments = array_filter(
            $tournamentRepository->getCommingTournaments(),
            function ($tournament) {
                return $this->isGranted(
                    "VIEW_CHAMPIONSHIP_TOURNAMENT",
                    $tournament
                );
            }
        );
        return $this->render("index/index.html.twig", [
            "activeChampionships" => $activeChampionships,
            "commingTournaments" => $commingTournaments,
            "controller_name" => "IndexController",
        ]);
    }
}

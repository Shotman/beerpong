<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Player;
use App\Entity\Tournament;
use App\Repository\PlayerRepository;
use App\Repository\TournamentRepository;
use App\Service\ChallongeService;
use App\Structs\Team;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TestController extends AbstractBeerpongController
{
    #[Route('/test/{championship}', name: 'app_test', env: 'dev', defaults: ['championship' => null])]
    public function index(
        Request $request,
        TournamentRepository $tournamentRepository,
        PlayerRepository $playerRepository,
        ?Championship $championship = null
    ): Response
    {
        $allChampionshipPlayers = $playerRepository->getAllPlayerByChampionShip($championship);
        $allTournaments = $tournamentRepository->findBy(['championship' => $championship]);
        usort($allChampionshipPlayers,fn(Player $playerA,Player $playerB) => $playerB->getTotalPointsByChampionship($championship) <=> $playerA->getTotalPointsByChampionship($championship));
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
            'championshipEntity' => $championship,
            'allChampionshipPlayers' => $allChampionshipPlayers,
            'allTournaments' => $allTournaments,
        ]);
    }
}

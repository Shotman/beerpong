<?php

namespace App\Controller;

use App\Entity\Tournament;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MercureTestController extends AbstractController
{
    #[Route('/mercure/test', name: 'app_mercure_test')]
    #[IsGranted("ROLE_SUPER_ADMIN")]
    public function index(HubInterface $hub): Response
    {
        $update = new Update(
            'toto',
            json_encode(["data"=>"THIS IS A TEST"]),
        );
        $hub->publish($update);
        return $this->render('mercure_test/index.html.twig', [
            'controller_name' => 'MercureTestController',
        ]);
    }

    #[NoReturn]
    #[Route('/mercure/test/tournament', name: 'app_mercure_test_tournament')]
    #[IsGranted("ROLE_SUPER_ADMIN")]
    public function tournament(Request $request, HubInterface $hub): void
    {
        $data = $request->request->all();
        $update = new Update(
            $data["tournament"],
            json_encode(["data"=>"DEBUT DU MATCH","content" => $data["team1"] . " VS " . $data["team2"]]),
        );
        $hub->publish($update);
    }
}

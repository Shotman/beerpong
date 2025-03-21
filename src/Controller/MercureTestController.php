<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            json_encode(["status"=>"OutOfStock","date"=>new \DateTime()]),
        );
        $hub->publish($update);
        return $this->render('mercure_test/index.html.twig', [
            'controller_name' => 'MercureTestController',
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Championship;
use App\Entity\Player;
use App\Entity\Tournament;
use App\Repository\PlayerRepository;
use App\Repository\TournamentRepository;
use App\Service\ChallongeService;
use App\Service\WebPushService;
use App\Structs\Team;
use Doctrine\Common\Collections\ArrayCollection;
use JetBrains\PhpStorm\NoReturn;
use Minishlink\WebPush\WebPush;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[IsGranted("ROLE_SUPER_ADMIN")]
class TestController extends AbstractController
{
    #[NoReturn] #[Route('/registerWebPushSub', name: 'app_registerWebPushSub', methods: ['POST'])]
    function registerWebPushSub(Request $request, WebPushService $webPush): JsonResponse
    {
        $webPush->registerSubscription(["context" => "test", "content" => $request->getContent()]);
        return new JsonResponse("OK");
    }

    #[Route('/sendWebPush', name: 'app_sendWebPush')]
    public function sendWebPush(Request $request, WebPushService $webPush, Packages $packages) {
        $webPush->sendPushNotification("TITRE","CONTENT","test");
    }
}

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
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test', env: 'dev')]
    public function index(
        Request $request,
        MailerInterface $mailer,
    ): Response
    {

        try{
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
        }catch (\Exception $e){
            dd($e);
        }
        dd('test');
    }
}

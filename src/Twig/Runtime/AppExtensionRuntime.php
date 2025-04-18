<?php

namespace App\Twig\Runtime;

use App\Entity\Tournament;
use App\Service\ChallongeService;
use App\Service\WebPushService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly KernelInterface $kernel,
                                private readonly ChallongeService $challongeService,
                                private CacheInterface $cacheRandom,
                                private readonly WebPushService $webPushService,
    )
    {
        // Inject dependencies if needed
    }

    public function getMaxIndex($collection)
    {
        if(empty($collection->children))
            return 0;
        return max(array_keys($collection->children)) + 1;
    }

    public function getCurrentRoute()
    {
        return $this->kernel->getContainer()->get('request_stack')->getCurrentRequest()->get('_route');
    }

    private function getSession(){
        return $this->kernel->getContainer()->get('request_stack')->getSession();
    }

    public function tournamentIsStarted(Tournament $tournament){
        $extraData = $tournament->getExtraData();
        if(array_key_exists("state",$extraData)){
            return $extraData["state"] !== "ended" ?? true;
        }
        return false;
    }

    public function userHasSubscribed(string $tournamentId){
        $session = $this->getSession()->getId();
        return $this->webPushService->hasSubscribed($session, $tournamentId);
    }


    public function adminCreatedTournament($user,$tournament)
    {
        if(in_array("ROLE_SUPER_ADMIN",$user->getRoles())){
            return true;
        }
        return $user === $tournament->getAdmin();
    }

    public function adminCreatedChampionship($user,$championship)
    {
        if(in_array("ROLE_SUPER_ADMIN",$user->getRoles())){
            return true;
        }
        return $user === $championship->getAdmin();
    }
}

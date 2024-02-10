<?php

namespace App\Twig\Runtime;

use App\Entity\Tournament;
use App\Service\ChallongeService;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly KernelInterface $kernel,
                                private readonly ChallongeService $challongeService,
                                private CacheInterface $cacheRandom
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

    public function tournamentIsStarted(Tournament $tournament){
        $extraData = $tournament->getExtraData();
        if(array_key_exists("state",$extraData)){
            return $extraData["state"] !== "ended" ?? true;
        }
        return false;
    }
}

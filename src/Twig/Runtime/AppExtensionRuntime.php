<?php

namespace App\Twig\Runtime;

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
        return max(array_keys($collection->children)) + 1;
    }

    public function getCurrentRoute()
    {
        return $this->kernel->getContainer()->get('request_stack')->getCurrentRequest()->get('_route');
    }

    public function tournamentIsStarted($tournamentId){
        return $this->cacheRandom->get("tournamentIsStarted".$tournamentId,function($item) use ($tournamentId){
            $item->expiresAfter(3600);
            $details = $this->challongeService->getTournamentDetails($tournamentId,true);
            return $details["raw"]->state !== "complete";
        });
    }
}

<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('getMaxIndex', [AppExtensionRuntime::class, 'getMaxIndex']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getCurrentRoute', [AppExtensionRuntime::class, 'getCurrentRoute']),
            new TwigFunction('tournamentIsStarted', [AppExtensionRuntime::class, 'tournamentIsStarted']),
            new TwigFunction('adminCreatedTournament', [AppExtensionRuntime::class, 'adminCreatedTournament']),
            new TwigFunction('adminCreatedChampionship', [AppExtensionRuntime::class, 'adminCreatedChampionship']),
            new TwigFunction('userHasSubscribed', [AppExtensionRuntime::class, 'userHasSubscribed']),
        ];
    }
}

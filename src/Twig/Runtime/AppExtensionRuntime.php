<?php

namespace App\Twig\Runtime;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly KernelInterface $kernel)
    {
        // Inject dependencies if needed
    }

    public function getCurrentRoute()
    {
        return $this->kernel->getContainer()->get('request_stack')->getCurrentRequest()->get('_route');
    }
}

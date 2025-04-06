<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

class BaseController extends AbstractController
{
    public function __construct(RequestStack $requestStack)
    {
        if (!$requestStack->getSession()->isStarted()) {
            $requestStack->getSession()->start();
            $requestStack->getSession()->has("persist");
        }
    }
}
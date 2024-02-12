<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

abstract class AbstractBeerpongController extends AbstractController
{
    public function __construct(Security $security, EntityManagerInterface $em)
    {
        if(!is_null($security->getUser()) && in_array("ROLE_ADMIN",$security->getUser()->getRoles()))
        {
            $em->getConfiguration()->addFilter('admin', 'App\Filters\AdminFilter');
            $filter = $em->getFilters()->enable('admin');
            $filter->setParameter('admin', $security->getUser()->getId());
        }
        if($security->getUser() === null){
            $em->getConfiguration()->addFilter('admin', 'App\Filters\AdminFilter');
            $filter = $em->getFilters()->enable('admin');
            $filter->setParameter('admin', NULL);
        }
        if(!is_null($security->getUser()) && in_array("ROLE_SUPER_ADMIN",$security->getUser()->getRoles()))
        {
            $em->getConfiguration()->addFilter('admin', 'App\Filters\AdminFilter');
            $em->getFilters()->disable('admin');
        }
    }
}
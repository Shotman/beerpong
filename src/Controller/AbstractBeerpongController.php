<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

abstract class AbstractBeerpongController extends AbstractController
{
    public function __construct(Security $security, EntityManagerInterface $em)
    {
        //Bar admin
        if(!is_null($security->getUser()) && in_array("ROLE_ADMIN",$security->getUser()->getRoles()))
        {
            $em->getConfiguration()->addFilter('admin', 'App\Filters\AdminFilter');
            $filter = $em->getFilters()->enable('admin');
            $filter->setParameter('admin', $security->getUser()->getId());
        }
        //Guest
        if($security->getUser() === null){
            $em->getConfiguration()->addFilter('admin', 'App\Filters\AdminFilter');
            $filter = $em->getFilters()->enable('admin');
            $filter->setParameter('admin', NULL);
        }
        //Super admin
        if(!is_null($security->getUser()) && in_array("ROLE_SUPER_ADMIN",$security->getUser()->getRoles()))
        {
            $em->getConfiguration()->addFilter('admin', 'App\Filters\AdminFilter');
            if($em->getFilters()->isEnabled("admin")){
                $em->getFilters()->disable('admin');
            }
        }
    }
}
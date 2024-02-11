<?php

namespace App\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class AdminFilter extends SQLFilter
{

    #[\Override] public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {

        if(!$targetEntity->getReflectionClass()->hasProperty('admin')){
            return '';
        }
        $baseCond = sprintf('%s.admin_id = %s', $targetTableAlias, $this->getParameter('admin'));
        if($this->getParameter('admin') !== null){
            return $baseCond.sprintf(' OR %s.admin_id IS NULL', $targetTableAlias);
        }
        return  $baseCond;
    }
}

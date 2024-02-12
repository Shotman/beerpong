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
        return sprintf('%s.admin_id = %s', $targetTableAlias, $this->getParameter('admin')).sprintf(' OR %s.public', $targetTableAlias);
    }
}

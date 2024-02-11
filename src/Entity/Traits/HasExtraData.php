<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasExtraData
{
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $extraData = null;

    public function getExtraData(): ?array
    {
        return $this->extraData;
    }

    public function setExtraData(array $extraData): static
    {
        $this->extraData = array_replace_recursive($this->extraData ?? [], $extraData);

        return $this;
    }
}
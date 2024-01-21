<?php

namespace App\Entity\Listener;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping\PreFlush;
use Doctrine\ORM\Mapping\PrePersist;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlayerListener
{

    public function __construct(private readonly ValidatorInterface $validator)
    {
    }


    #[PrePersist]
    public function prePersist(object $entity, PrePersistEventArgs $eventArgs): void
    {
        $entity->setIdentifier($this->generateIdentifier($entity->getName()));
    }

    #[PreFlush]
    public function preFlush(object $entity, PreFlushEventArgs $eventArgs): void
    {
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            throw new ValidationFailedException($entity, $errors);
        }
    }
    private function generateIdentifier(string $name): string
    {
        $slugger = new AsciiSlugger();
        return $slugger->slug($name)->lower()->toString();
    }

}
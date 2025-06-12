<?php

namespace App\Repository;
use Doctrine\ORM\EntityRepository;
use src\Entity\User;
use Exception;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use \Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface {
    /**
     * Для дополнительных методов
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface {
        return $this->findOneBy(["email" => $identifier]);
    }
}
<?php

namespace App\Doctrine;

use App\Entity\User\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;

final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []): void
    {
//        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $resourceClass
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        /** @var User $user */
//        $user = $this->security->getUser();

//        $rootAlias = $queryBuilder->getRootAliases()[0];

    }

}

<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractWebantFilter extends AbstractContextAwareFilter
{
    /**
     * @param $fields
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param Composite|null $composite - you can add filters to orX/andX, for example, even nested ones
     * @param bool $appendComposite - use can call this twice for the same queryBuilder. use it if you call it only once on the same composite
     */
    protected function filterFields($fields, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Composite $composite = null, $appendComposite = false): void
    {
        foreach ($fields as $path => $value) {
            $this->filterProperty($path, $value, $queryBuilder, $queryNameGenerator, $resourceClass, $composite);
        }
        if ($appendComposite) {
            $queryBuilder->andWhere($composite);
        }
    }

    /**
     * prepare property for filtering.
     * those lines are copy-pasted multiple times in \ApiPlatform\Core\Bridge\Doctrine\Orm\Filter(api-platform/core v2.4.2).
     * @param string $property
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @return array
     */
    protected function preparePropertyFilter(string $property, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): array
    {
        $alias = $queryBuilder->getRootAliases()[0];
        $field = $property;

        $associations = [];
        if ($this->isPropertyNested($property, $resourceClass)) {
            [$alias, $field, $associations] = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator, $resourceClass);
        }
        $valueParameter = $queryNameGenerator->generateParameterName($field);

        return [$alias, $field, $valueParameter, $associations];
    }

    /**
     * @param string $property
     * @param $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName - not used, just to comply with parent class
     * @param Composite|null $composite - you can add filters to orX/andX, for example, even nested ones
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null, ?Composite $composite = null)
    {
        if (!$this->isPropertyMapped($property, $resourceClass, true)) {
            return;
        }
        [$alias, $field, $valueParameter] = $this->preparePropertyFilter($property, $queryBuilder, $queryNameGenerator, $resourceClass);

        if (null !== $value) {
            $queryBuilder->setParameter($valueParameter, $value);
        }

        switch (gettype($value)) {
            case 'NULL':
                // null is handled in special way
                $str = (sprintf('%s.%s IS NULL', $alias, $field));
                break;
            case 'array':
                $str = sprintf('%s.%s IN(:%s)', $alias, $field, $valueParameter);
                if (in_array(null, $value, true)) {
                    $str .= sprintf(' OR %s.%s IS NULL', $alias, $field);
                }
                break;
            default:
                $str = (sprintf('%s.%s = :%s', $alias, $field, $valueParameter));
                break;
        }

        if ($composite instanceof Composite) {
            $composite->add($str);
        } else {
            $queryBuilder->andWhere($str);
        }
    }

    protected function getFilters(array $context = [])
    {
        $filters = $context['filters'] ?? [];
        // apiPlatform is migrating from getting filters from requestStack to getting filters from context key
        // currently(v2.4) it supports both ways so I support both too
        if (empty($filters) && $this->requestStack) {
            $request = $this->requestStack->getCurrentRequest();
            $filters = $request->query->all();
        }

        return $filters;
    }

    /**
     * filter by property != value.
     *
     * @param string $property
     * @param $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param Composite|null $composite - you can add filters to orX/andX, for example, even nested ones
     * @param string|null $joinType - there are some issues when search by deep property
     */
    protected function filterNegative(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Composite $composite = null, $joinType = Join::LEFT_JOIN): void
    {
        if (!$this->isPropertyMapped($property, $resourceClass, true)) {
            return;
        }
        [$alias, $field, $valueParameter] = $this->preparePropertyFilter($property, $queryBuilder, $queryNameGenerator, $resourceClass);

        if (null === $value) {
            $dql = sprintf('%s.%s IS NOT NULL', $alias, $field);
        } else {
            $queryBuilder->setParameter($valueParameter, $value);
            $dql = sprintf('%s.%s != :%s', $alias, $field, $valueParameter);
        }

        if ($composite instanceof Composite) {
            $composite->add($dql);
        } else {
            $queryBuilder->andWhere($dql);
        }
    }
}

<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\User\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

use function in_array;

/**
 * Фильтр был изменен под проект
 */
final class MyFilter extends AbstractWebantFilter
{
    private $security;

    public const PARAMETER_ME = 'me';

    public function __construct(ManagerRegistry $managerRegistry,
        ?RequestStack $requestStack = null,
        LoggerInterface $logger = null,
        array $properties = null,
        Security $security = null)
    {
        $this->security = $security;
        $this->logger = $logger;

        parent::__construct($managerRegistry, $requestStack, $logger, $properties);
    }

    public function apply(QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []): void
    {
        $filters = $this->getFilters($context);

        /** @var User $user */
        $user = $this->security->getUser();

        /* Изменил фильтр под свои задачи */
        $userId = $user ? $user->getId() : null;

        foreach ($this->properties as $property => $_) {
            $value = $this->normalizeValue($filters[$property][self::PARAMETER_ME] ?? null, $property);

            if ($value) {
                $this->filterProperty($property,
                    $userId,
                    $queryBuilder,
                    $queryNameGenerator,
                    $resourceClass,
                    $operationName);
            } elseif (false === $value) {
                $this->filterNegative($property, $userId, $queryBuilder, $queryNameGenerator, $resourceClass);
            }
        }
    }

    /**
     * Gets the description of this filter for the given resource.
     * Returns an array with the filter parameter names as keys and array with the following data as values:
     *   - property: the property where the filter is applied
     *   - type: the type of the filter
     *   - required: if this filter is required
     *   - strategy: the used strategy
     *   - is_collection (optional): is this filter is collection
     *   - swagger (optional): additional parameters for the path operation,
     *     e.g. 'swagger' => [
     *       'description' => 'My Description',
     *       'name' => 'My Name',
     *       'type' => 'integer',
     *     ]
     *   - openapi (optional): additional parameters for the path operation in the version 3 spec,
     *     e.g. 'openapi' => [
     *       'description' => 'My Description',
     *       'name' => 'My Name',
     *       'schema' => [
     *          'type' => 'integer',
     *       ]
     *     ]
     * The description can contain additional data specific to a filter.
     * @param string $resourceClass
     * @return array
     * @see \ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer::getFiltersParameters
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        $properties = $this->getProperties();
        if (!is_array($properties)) {
            return [];
        }
        foreach ($properties as $property => $strategy) {
            $description += $this->getFilterDescription($property);
        }

        return $description;
    }

    /**
     * Gets filter description.
     * @param string $fieldName
     * @param string $operator
     * @return array
     */
    protected function getFilterDescription(string $fieldName, string $operator = self::PARAMETER_ME): array
    {
        return [
            sprintf('%s[%s]', $fieldName, $operator) => [
                'property' => $fieldName,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }

    private function normalizeValue($value, string $property): ?bool
    {
        if (in_array($value, [false, 'false', '0', 0, null], true)) {
            return null;
        }

        if (in_array($value, [true, 'true', '1', 1], true)) {
            return true;
        }

        $this->getLogger()->notice('Invalid filter ignored',
            [
                'exception' => new InvalidArgumentException(sprintf('Invalid boolean value for "%s" property, expected one of ( "%s" )',
                    $property,
                    implode('" | "',
                        [
                            'true',
                            'false',
                            '1',
                            '0',
                        ]))),
            ]);

        return null;
    }
}

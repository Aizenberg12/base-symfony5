<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Serializer\AbstractCollectionNormalizer;
use ApiPlatform\Core\Util\IriHelper;
use ApiPlatform\Core\DataProvider\PartialPaginatorInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


/**
 * Normalizes collections in the JSON API format.
 */
final class ApiNormalizer extends AbstractCollectionNormalizer
{

    public const FORMAT = 'json';

    /**
     * {@inheritdoc}
     */
    protected function getPaginationData($object, array $context = []): array
    {
        if (!$object instanceof PartialPaginatorInterface) {
            return [];
        }
        [
            $paginator,
            $paginated,
            $currentPage,
            $itemsPerPage,
            $lastPage,
            $pageTotalItems,
            $totalItems,
        ] = $this->getPaginationConfig($object, $context);

        if (!$paginator) {
            return [];
        }

        if (null !== $totalItems) {
            $items['totalItems'] = $totalItems;
        }
        if (null !== $itemsPerPage) {
            $items['itemsPerPage'] = $itemsPerPage;
        }
        $countOfPages = ceil($totalItems / $itemsPerPage);
        if (null !== $countOfPages) {
            $items['countOfPages'] = $countOfPages;
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    protected function getItemsData($object, string $format = null, array $context = []): array
    {
        if (!$object instanceof PartialPaginatorInterface) {
            return $object;
        }
        $data = [
            'items' => [],
        ];

        foreach ($object as $obj) {
            $item = $this->normalizer->normalize($obj, $format, $context);
            $data['items'][] = $item;
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use function array_merge;
use function is_array;

final class ContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;
    private $security;

    public function __construct(SerializerContextBuilderInterface $decorated, Security $security)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    /**
     * @param Request $request
     * @param bool $normalization
     * @param null|array $extractedAttributes
     * @return array
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {

        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $adminGroups = $context['adminGroups'] ?? null;
        if (is_array($adminGroups) && $this->security->isGranted('ROLE_ADMIN')) {
            $context['groups'] = array_merge($context['groups'], $adminGroups);
        }

        return $context;
    }
}

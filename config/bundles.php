<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle::class => ['all' => true],
    Webant\UserBundle\WebantUserBundle::class => ['all' => true],
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],
    Webant\BaseBundle\WebantBaseBundle::class => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    FOS\OAuthServerBundle\FOSOAuthServerBundle::class => ['all' => true],
    League\FlysystemBundle\FlysystemBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
];

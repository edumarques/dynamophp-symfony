<?php

declare(strict_types=1);

namespace EduardoMarques\DynamoPHPBundle;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use EduardoMarques\DynamoPHP\Metadata\MetadataLoader;
use EduardoMarques\DynamoPHP\ODM\EntityManager;
use EduardoMarques\DynamoPHP\ODM\OpArgsBuilder;
use EduardoMarques\DynamoPHP\Serializer\EntityDenormalizer;
use EduardoMarques\DynamoPHP\Serializer\EntityNormalizer;
use EduardoMarques\DynamoPHP\Serializer\EntitySerializer;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class DynamoPHPBundle extends AbstractBundle
{
    /**
     * @inheritDoc
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        /** @phpstan-ignore-next-line */
        $definition->rootNode()
            ->children()
            ->variableNode('client')->end()
            ->variableNode('marshaler')->end()
            ->end();
    }

    /**
     * @inheritDoc
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        if (
            false === $builder->hasDefinition(DynamoDbClient::class)
            && false === $builder->hasAlias(DynamoDbClient::class)
        ) {
            $services->set(DynamoDbClient::class)
                ->factory([DynamoDbClient::class, 'factory'])
                ->arg(0, $config['client'] ?? [])
                ->public();
        }

        if (
            false === $builder->hasDefinition(Marshaler::class)
            && false === $builder->hasAlias(Marshaler::class)
        ) {
            $services->set(Marshaler::class)
                ->arg(0, $config['marshaler'] ?? [])
                ->public();
        }

        $services->set(MetadataLoader::class)
            ->autowire()
            ->autoconfigure()
            ->private();

        $services->set(EntityNormalizer::class)
            ->autowire()
            ->autoconfigure()
            ->public();

        $services->set(EntityDenormalizer::class)
            ->autowire()
            ->autoconfigure()
            ->public();

        $services->set(EntitySerializer::class)
            ->autowire()
            ->autoconfigure()
            ->public();

        $services->set(OpArgsBuilder::class)
            ->autowire()
            ->autoconfigure()
            ->public();

        $services->set(EntityManager::class)
            ->autowire()
            ->autoconfigure()
            ->public();
    }
}

<?php

declare(strict_types=1);

namespace EduardoMarques\DynamoPHPBundle;

use EduardoMarques\DynamoPHP\Metadata\MetadataLoader;
use EduardoMarques\DynamoPHP\ODM\EntityManager;
use EduardoMarques\DynamoPHP\ODM\OpArgsBuilder;
use EduardoMarques\DynamoPHP\Serializer\EntityDenormalizer;
use EduardoMarques\DynamoPHP\Serializer\EntityNormalizer;
use EduardoMarques\DynamoPHP\Serializer\EntitySerializer;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
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
                ->scalarNode('client')->info('Service ID of the AWS DynamoDB client to use')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('marshaler')->info('Service ID of the AWS Marshaler to use')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('serializer')->info('Service ID of the Symfony Serializer to use')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

    /**
     * @inheritDoc
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $clientId = $config['client'];
        $marshalerId = $config['marshaler'];
        $serializerId = $config['serializer'];

        $services = $container->services();

        $services->set(MetadataLoader::class)->autowire()->autoconfigure();

        $services->set(EntityNormalizer::class)
            ->arg('$metadataLoader', new ReferenceConfigurator(MetadataLoader::class))
            ->arg('$normalizer', new ReferenceConfigurator($serializerId))
            ->autowire()
            ->autoconfigure();

        $services->set(EntityDenormalizer::class)
            ->arg('$metadataLoader', new ReferenceConfigurator(MetadataLoader::class))
            ->arg('$denormalizer', new ReferenceConfigurator($serializerId))
            ->autowire()
            ->autoconfigure();

        $services->set(EntitySerializer::class)
            ->arg('$entityNormalizer', new ReferenceConfigurator(EntityNormalizer::class))
            ->arg('$entityDenormalizer', new ReferenceConfigurator(EntityDenormalizer::class))
            ->arg('$marshaler', new ReferenceConfigurator($marshalerId))
            ->autowire()
            ->autoconfigure();

        $services->set(OpArgsBuilder::class)
            ->arg('$normalizer', new ReferenceConfigurator($serializerId))
            ->arg('$marshaler', new ReferenceConfigurator($marshalerId))
            ->autowire()
            ->autoconfigure();

        $services->set(EntityManager::class)
            ->arg('$dynamoDbClient', new ReferenceConfigurator($clientId))
            ->arg('$metadataLoader', new ReferenceConfigurator(MetadataLoader::class))
            ->arg('$entitySerializer', new ReferenceConfigurator(EntitySerializer::class))
            ->arg('$opArgsBuilder', new ReferenceConfigurator(OpArgsBuilder::class))
            ->autowire()
            ->autoconfigure();
    }
}

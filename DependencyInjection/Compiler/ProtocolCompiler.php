<?php

declare(strict_types=1);

namespace Wizzaq\RestBundle\DependencyInjection\Compiler;

use Wizzaq\RestBundle\Protocol\ProtocolRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProtocolCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ProtocolRegistry::class)) {
            return;
        }
        $definition = $container->getDefinition(ProtocolRegistry::class);

        $serializer = match (true) {
            null !== $container->getParameter('wizzaq_rest.protocol.serializer') =>
                $container->getParameter('wizzaq_rest.protocol.serializer'),
            $container->hasDefinition('jms_serializer') => new Reference('jms_serializer'),
            $container->hasDefinition('serializer') => new Reference('serializer'),
            default => null,
        };
        $container->getParameterBag()->remove('wizzaq_rest.protocol.serializer');

        foreach ($container->findTaggedServiceIds('wizzaq_rest.protocol') as $id => $tags) {
            foreach ($tags as $tag) {
                $definition->addMethodCall('addProtocol', [new Reference($id), $tag['alias'] ?? null]);
            }

            $protocolDefinition = $container->getDefinition($id);
            $bindings = $protocolDefinition->getBindings();

            if (null !== $serializer) {
                $bindings['$serializer'] ??= $serializer;
            }

            $protocolDefinition->setBindings($bindings);
        }
    }
}

<?php

declare(strict_types=1);

namespace Wizzaq\RestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Wizzaq\RestBundle\Protocol\NamedProtocolInterface;

class WizzaqRestExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.php');

        if ($config['use_resolvers']) {
            $loader->load('resolvers/services.php');
        }

        if ($config['use_protocols']) {
            $loader->load('protocols/services.php');

            $container->setParameter('wizzaq_rest.default_protocol', $config['default_protocol']);
            $container->setParameter('wizzaq_rest.default_response_section', $config['default_response_section']);
            $container->setParameter('wizzaq_rest.protocol.serializer', null !== $config['serializer']
                ? new Reference($config['serializer'])
                : null
            );

            foreach ($config['protocols'] ?? [] as $protocol => $enabled) {
                if ($enabled) {
                    $loader->load("protocols/$protocol.php");
                }
            }

            $container->registerForAutoconfiguration(NamedProtocolInterface::class)
                ->addTag('wizzaq_rest.protocol');
        }
    }
}

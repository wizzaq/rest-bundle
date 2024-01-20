<?php

namespace Wizzaq\RestBundle\DependencyInjection\Loader\Configurator\Protocols;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\KernelEvents;
use Wizzaq\RestBundle\EventListener\ProtocolListener;
use Wizzaq\RestBundle\Config\RestConfig;
use Wizzaq\RestBundle\Protocol\ProtocolRegistry;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(ProtocolRegistry::class)
            ->args([
                param('wizzaq_rest.default_protocol')
            ])

        ->set(ProtocolListener::class)
            ->args([
                service(ProtocolRegistry::class),
                service(RestConfig::class),
                param('wizzaq_rest.default_response_section'),
            ])
            ->tag('kernel.event_listener', ['event' => KernelEvents::CONTROLLER])
            ->tag('kernel.event_listener', ['event' => KernelEvents::VIEW])
            ->tag('kernel.event_listener', ['event' => KernelEvents::EXCEPTION])
    ;
};

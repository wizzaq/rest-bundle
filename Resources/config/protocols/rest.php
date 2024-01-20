<?php

namespace Wizzaq\RestBundle\DependencyInjection\Loader\Configurator\Protocols;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Wizzaq\RestBundle\Config\RestConfig;
use Wizzaq\RestBundle\Protocol\RestProtocol;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(RestProtocol::class)
            ->args([
                service(RestConfig::class),
                param('kernel.debug')
            ])
            ->tag('wizzaq_rest.protocol', ['alias' => 'rest'])
    ;
};

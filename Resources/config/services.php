<?php

namespace Wizzaq\RestBundle\DependencyInjection\Loader\Configurator\Protocols;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Wizzaq\RestBundle\Config\RestConfig;
use Wizzaq\RestBundle\Serializer\CircularReferenceHandler;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(RestConfig::class)
            ->args([
                '_rest'
            ])
        ->alias('wizzaq_rest.config', RestConfig::class)

        ->set('wizzaq_rest.serializer.circular_reference_handler', CircularReferenceHandler::class)
            ->args([
                service('doctrine')->nullOnInvalid()
            ])
    ;
};

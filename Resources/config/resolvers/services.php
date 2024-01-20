<?php

namespace Wizzaq\RestBundle\DependencyInjection\Loader\Configurator\Protocols;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Wizzaq\RestBundle\ArgumentResolver\ProcessFormResolver;
use Wizzaq\RestBundle\Config\RestConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('wizzaq_rest.argument_resolver.process_form_resolver', ProcessFormResolver::class)
            ->args([
                service('doctrine.orm.entity_value_resolver'),
                service('doctrine'),
                service('form.factory'),
                service(RestConfig::class),
            ])
            ->tag('controller.argument_value_resolver', ['priority' => 150, 'name' => ProcessFormResolver::class])
    ;
};

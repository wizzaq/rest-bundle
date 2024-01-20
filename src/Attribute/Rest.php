<?php

namespace Wizzaq\RestBundle\Attribute;

use function is_string;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Rest
{
    public ?array $routes = null;
    public ?array $responseSerializationGroups = null;

    public function __construct(
        null|string|array $routes = null,
        public ?string    $protocol = null,
        public ?string    $responseSection = null,
        null|string|array $responseSerializationGroups = null,
    ) {
        $this->routes = is_string($routes)
            ? [$routes]
            : $routes;
        $this->responseSerializationGroups = is_string($responseSerializationGroups)
            ? [$responseSerializationGroups]
            : $responseSerializationGroups;
    }
}

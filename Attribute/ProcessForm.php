<?php

namespace Wizzaq\RestBundle\Attribute;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Wizzaq\RestBundle\ArgumentResolver\ProcessFormResolver;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class ProcessForm extends MapEntity
{
    public function __construct(
        /** @psalm-param class-string $form */
        public ?string $form,
        public ?bool $mapEntity = null,
        public ?bool $submit = false,
        public ?bool $throwOnNotValid = true,
        ?string $class = null,
        ?string $objectManager = null,
        ?string $expr = null,
        ?array $mapping = null,
        ?array $exclude = null,
        ?bool $stripNull = null,
        array|string|null $id = null,
        ?bool $evictCache = null,
        bool $disabled = false,
        string $resolver = ProcessFormResolver::class
    ){
        parent::__construct($class, $objectManager, $expr, $mapping, $exclude, $stripNull, $id, $evictCache, $disabled, $resolver);
    }
}

<?php

namespace Wizzaq\RestBundle\Serializer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\MappingException;

use function array_values;
use function count;
use function method_exists;
use function spl_object_id;

class CircularReferenceHandler
{
    public function __construct(protected ?ManagerRegistry $registry = null)
    {
    }

    public function __invoke($object)
    {
        if (null === $manager = $this->registry?->getManagerForClass($object::class)) {
            return $this->fallbackResolve($object);
        }

        try {
            $meta = $manager->getClassMetadata($object::class);
            $id = $meta->getIdentifierValues($object);

            return count($id) === 1
                ? array_values($id)[0]
                : $id;
        } catch (MappingException) {
            return $this->fallbackResolve($object);
        }
    }

    protected function fallbackResolve($object): string
    {
        return method_exists($object, '__toString')
            ? $object->__toString()
            : spl_object_id($object);
    }
}

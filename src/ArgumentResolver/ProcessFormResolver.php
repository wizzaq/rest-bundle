<?php

namespace Wizzaq\RestBundle\ArgumentResolver;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wizzaq\RestBundle\Attribute\ProcessForm;
use Wizzaq\RestBundle\Exception\FormValidationException;
use Wizzaq\RestBundle\Config\RestConfig;

use function array_is_list;
use function array_combine;
use function count;
use function is_array;
use function is_object;
use function sprintf;

class ProcessFormResolver implements ValueResolverInterface
{
    public function __construct(
        private EntityValueResolver $entityValueResolver,
        private ManagerRegistry $registry,
        private FormFactoryInterface $formFactory,
        private RestConfig $restConfig,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $options = $argument->getAttributes(ProcessForm::class, ArgumentMetadata::IS_INSTANCEOF);

        /** @var ProcessForm $options */
        if (null === $options = ($options[0] ?? null)) {
            return [];
        }
        $options = $options->withDefaults($options, $argument->getType());

        if (!$options->form || $options->disabled) {
            return [];
        }

        if (!is_object($object = $request->attributes->get($argument->getName()))) {
            $object = null;
        }
        if (null === $object && $this->shouldLoadEntity($request, $options, $argument)) {
            $object = $this->entityValueResolver->resolve($request, $argument)[0] ?? null;

            if (null === $object && !$argument->isNullable()) {
                throw new NotFoundHttpException(sprintf('"%s" object not found by "%s".', $options->class, self::class));
            }
        }

        $form = $this->formFactory->create(
            $options->form,
            $object,
            !$request->isMethod('GET') ? ['method' => $request->getMethod()] : []
        );
        $this->restConfig->setProcessedForm($request, $form);

        if (!$options->submit) {
            $form->handleRequest($request);
        } else {
            $requestPart = $form->getConfig()->getMethod() === 'GET' ? 'query' : 'request';
            $data = '' === $form->getName()
                ? $request->{$requestPart}->all()
                : $request->{$requestPart}->get($form->getName());

            $form->submit($data, false);
        }

        if (!$form->isSubmitted()) {
            if (null === $object && !$argument->isNullable()) {
                throw new NotFoundHttpException(sprintf('"%s" object not found by "%s".', $options->class, self::class));
            }

            return [$object];
        }

        $object = $form->getData();

        if (!$form->isValid()) {
            if ($options->throwOnNotValid) {
                throw new FormValidationException($form, 'Validation failed.');
            }
            if (null === $object && !$argument->isNullable()) {
                throw new NotFoundHttpException(sprintf('"%s" object not found by "%s".', $options->class, self::class));
            }

            return [$object];
        }

        return [$form->getData()];
    }

    private function shouldLoadEntity(Request $request, ProcessForm $options, ArgumentMetadata $argument): bool
    {
        if (null === $manager = $this->getManager($options->objectManager, $options->class)) {
            return false;
        }
        if (null !== $options->mapEntity) {
            return $options->mapEntity;
        }
        if (null !== $options->expr) {
            return true;
        }
        if ($this->hasIdentifier($request, $options, $argument->getName())) {
            return true;
        }
        if ($this->hasCriteria($request, $options, $manager)) {
            return true;
        }

        return false;
    }


    private function getManager(?string $name, string $class): ?ObjectManager
    {
        if (null === $name) {
            return $this->registry->getManagerForClass($class);
        }

        try {
            $manager = $this->registry->getManager($name);
        } catch (\InvalidArgumentException) {
            return null;
        }

        return $manager->getMetadataFactory()->isTransient($class) ? null : $manager;
    }

    /**
     * @see EntityValueResolver::getIdentifier()
     */
    private function hasIdentifier(Request $request, ProcessForm $options, string $name): bool
    {
        if (\is_array($options->id)) {
            return true;
        }

        if (null !== $options->id) {
            $name = $options->id;
        }

        if ($request->attributes->has($name)) {
            return true;
        }

        if (!$options->id && $request->attributes->has('id')) {
            return true;
        }

        return false;
    }

    /**
     * @see EntityValueResolver::getCriteria()
     */
    private function hasCriteria(Request $request, ProcessForm $options, ObjectManager $manager): bool
    {
        if (null === $mapping = $options->mapping) {
            $mapping = $request->attributes->keys();
        }

        if ($mapping && is_array($mapping) && array_is_list($mapping)) {
            $mapping = array_combine($mapping, $mapping);
        }

        foreach ($options->exclude as $exclude) {
            unset($mapping[$exclude]);
        }

        if (!$mapping) {
            return false;
        }

        $criteria = [];
        $metadata = $manager->getClassMetadata($options->class);

        foreach ($mapping as $attribute => $field) {
            if (!$metadata->hasField($field) && (!$metadata->hasAssociation($field) || !$metadata->isSingleValuedAssociation($field))) {
                continue;
            }

            $criteria[$field] = $request->attributes->get($attribute);
        }

        return count($criteria) > 0;
    }
}

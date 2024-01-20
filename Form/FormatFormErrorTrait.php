<?php

namespace Wizzaq\RestBundle\Form;

use Symfony\Component\Form\FormInterface;

trait FormatFormErrorTrait
{
    protected function formatFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $name = $error->getOrigin()?->getPropertyPath()?->getElements() ?? [$form->getName()];

            $errors[join('.', $name)][] = $error->getMessage();
        }

        return $errors;
    }
}

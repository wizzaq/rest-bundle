<?php

namespace Wizzaq\RestBundle\Exception;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Wizzaq\RestBundle\Form\FormatFormErrorTrait;

class FormCollectionValidationException extends UnprocessableEntityHttpException
{
    use FormatFormErrorTrait;

    private array $errors = [];

    public function __construct(array $forms = [], string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);

        foreach ($forms as $key => $form) {
            $this->addForm($form, $key);
        }
    }

    public function addForm(FormInterface $form, int $key): static
    {
        $this->errors[$key] = $this->formatFormErrors($form);

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

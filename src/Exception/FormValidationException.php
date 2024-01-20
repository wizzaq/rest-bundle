<?php

namespace Wizzaq\RestBundle\Exception;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Wizzaq\RestBundle\Form\FormatFormErrorTrait;

class FormValidationException extends UnprocessableEntityHttpException
{
    use FormatFormErrorTrait;

    public function __construct(private FormInterface $form,
                                string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }

    public function getErrors(): array
    {
        return $this->formatFormErrors($this->form);
    }
}

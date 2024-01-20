<?php

declare(strict_types=1);

namespace Wizzaq\RestBundle\Protocol;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Wizzaq\RestBundle\Config\RestConfig;
use Wizzaq\RestBundle\Exception\FormValidationException;

use function trigger_error;

class RestProtocol implements ProtocolInterface
{
    private \Symfony\Component\Serializer\SerializerInterface|\JMS\Serializer\Serializer|null $serializer;

    public function __construct(
        private RestConfig $restConfig,
        private bool $debug = false,
        $serializer = null
    ) {
        if (null !== $serializer && !$serializer instanceof \Symfony\Component\Serializer\SerializerInterface && !$serializer instanceof \JMS\Serializer\Serializer) {
            throw new \InvalidArgumentException(sprintf('$serializer should be instance of Symfony\Component\Serializer\SerializerInterface or JMS\Serializer\Serializer. "%s" given.', get_class($serializer)));
        }

        $this->serializer = $serializer;
    }

    public function processRequest(Request $request): void
    {
        $params = null;

        if ('' != $request->getContent()) {
            $params = $request->toArray();
        }

        if (null === $params) {
            return;
        }

        $request->request->replace($params);
    }

    public function processResponse($response, Request $request): Response
    {
        return $this->serializeResponse($response, 200, [], $this->restConfig->responseSerializationGroups($request));
    }

    public function processException(\Throwable $exception): Response
    {
        $data = ['message' => $exception->getMessage()];

        if ($exception instanceof FormValidationException) {
            $data['errors'] = $exception->getErrors();
        }

        if ($this->debug) {
            $data['trace'] = $exception->getTraceAsString();
        }

        return $this->serializeResponse(
            $data,
            $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500,
            $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : []
        );
    }

    private function serializeResponse(mixed $data, int $status = 200, array $headers = [], ?array $serializationGroups = null): Response
    {
        if (null === $this->serializer) {
            return new JsonResponse($data, $status, $headers);
        }

        // @todo add context
        if ($this->serializer instanceof \Symfony\Component\Serializer\SerializerInterface) {
            $context = [];

            if (null !== $serializationGroups) {
                $context['groups'] = $serializationGroups;
            }
        } elseif ($this->serializer instanceof \JMS\Serializer\Serializer) {
            trigger_error('not implemented');
            $context = null;
        }

        $json = $this->serializer->serialize($data, 'json', $context);

        return new JsonResponse($json, $status, $headers, true);
    }
}

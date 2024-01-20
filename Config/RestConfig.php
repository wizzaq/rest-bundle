<?php

namespace Wizzaq\RestBundle\Config;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Wizzaq\RestBundle\Attribute\Rest;

use function array_reduce;
use function in_array;

class RestConfig
{
    public function __construct(private string $prefix)
    {
    }

    public function setConfig(Request $request, array $attributes): void
    {
        if ([] === $rest = ($attributes[Rest::class] ?? [])) {
            return;
        }
        $route = $request->attributes->get('_route');

        $rest = array_reduce(
            $rest,
            static function (?Rest $result, Rest $curr) use ($route): ?Rest {
                if (null !== $curr->routes && !in_array($route, $curr->routes)) {
                    return $result;
                }

                if (null === $result) {
                    $result = $curr;
                }

                if (null !== $curr->protocol) {
                    $result->protocol = $curr->protocol;
                }
                if (null !== $curr->responseSection) {
                    $result->responseSection = $curr->responseSection;
                }
                if (null !== $curr->responseSerializationGroups) {
                    $result->responseSerializationGroups = $curr->responseSerializationGroups;
                }

                return $result;
            },
            null
        );

        /** @var ?Rest $rest */
        if (null === $rest) {
            return;
        }

        $params = $request->attributes->get('_route_params');

        $params[$this->prefix] = true;

        if ($rest->protocol) {
            $params[$this->prefix . '_protocol'] = $rest->protocol;
        }
        if ($rest->responseSection) {
            $params[$this->prefix . '_response_section'] = $rest->responseSection;
        }
        if ($rest->responseSerializationGroups) {
            $params[$this->prefix . '_response_serialization_groups'] = $rest->responseSerializationGroups;
        }

        $request->attributes->set('_route_params', $params);
    }

    public function setProcessedForm(Request $request, FormInterface $form): void
    {
        $params = $request->attributes->get('_route_params');
        $params[$this->prefix . '_processed_form'] = $form;
        $request->attributes->set('_route_params', $params);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function isRest(Request $request): bool
    {
        return (bool)($request->attributes->get('_route_params')[$this->prefix] ?? false);
    }

    public function protocolName(Request $request): ?string
    {
        return $request->attributes->get('_route_params')[$this->prefix . '_protocol'] ?? null;
    }

    public function responseSection(Request $request, ?string $default = null): ?string
    {
        return $request->attributes->get('_route_params')[$this->prefix . '_response_section'] ?? $default;
    }

    public function responseSerializationGroups(Request $request): ?array
    {
        return $request->attributes->get('_route_params')[$this->prefix . '_response_serialization_groups'] ?? null;
    }

    public function processedForm(Request $request): ?FormInterface
    {
        return $request->attributes->get('_route_params')[$this->prefix . '_processed_form'] ?? null;
    }

    public function get(Request $request, string $param, mixed $default = null, ?string $type = null): mixed
    {
        $value = $request->attributes->get('_route_params')[$this->prefix . '_' . $param] ?? $default;

        if (null !== $type) {
            settype($value, $type);
        }

        return $value;
    }
}

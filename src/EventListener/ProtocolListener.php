<?php

declare(strict_types=1);

namespace Wizzaq\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Wizzaq\RestBundle\Config\RestConfig;
use Wizzaq\RestBundle\Protocol\ProtocolRegistry;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;

use function is_array;

class ProtocolListener
{
    public function __construct(
        private ProtocolRegistry $protocolRegistry,
        private RestConfig $restConfig,
        private ?string $responseSection = null,
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();

        $this->restConfig->setConfig($request, $event->getAttributes());

        if (!$this->restConfig->isRest($request)) {
            return;
        }

        $this->protocolRegistry->getProtocol($this->restConfig->protocolName($request))
            ->processRequest($request);
    }

    public function onKernelView(ViewEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !$this->restConfig->isRest($request)) {
            return;
        }

        $response = $event->getControllerResult();
        $response =
            (is_array($response) || $response instanceof \ArrayAccess)
            && null !== ($section = $this->restConfig->responseSection($request, $this->responseSection))
                ? ($response[$section] ?? null)
                : $response;

        $response = $this->protocolRegistry->getProtocol($this->restConfig->protocolName($request))
            ->processResponse($response, $request);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !$this->restConfig->isRest($request)) {
            return;
        }

        $response = $this->protocolRegistry->getProtocol($this->restConfig->protocolName($request))
            ->processException($event->getThrowable());

        $event->setResponse($response);
        $event->stopPropagation();
    }
}

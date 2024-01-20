<?php

declare(strict_types=1);

namespace Wizzaq\RestBundle\Protocol;

use function array_key_exists;
use function get_class;
use function sprintf;

final class ProtocolRegistry
{
    /**
     * @var ProtocolInterface[]
     */
    private array $protocols = [];

    public function __construct(private ?string $default = null)
    {
    }

    public function addProtocol(ProtocolInterface $protocol, ?string $alias = null): self
    {
        if (null === $alias) {
            if (!$protocol instanceof NamedProtocolInterface) {
                throw new \InvalidArgumentException('Acceptable only Wizzaq\RestBundle\Protocol\NamedProtocolInterface or defined "alias" parameter.');
            }
            $alias = $protocol->getProtocolName();
        }

        if (array_key_exists($alias, $this->protocols)) {
            throw new \InvalidArgumentException(sprintf('Protocol "%s" already defined as "%s".', $alias, get_class($protocol)));
        }

        $this->protocols[$alias] = $protocol;

        if (null === $this->default) {
            $this->default = $alias;
        }

        return $this;
    }

    public function getProtocol(?string $alias = null): ProtocolInterface
    {
        $alias ??= $this->default;

        if (null === ($this->protocols[$alias] ?? null)) {
            throw new \InvalidArgumentException(sprintf('Protocol "%s" has not defined.', $alias));
        }

        return $this->protocols[$alias];
    }
}

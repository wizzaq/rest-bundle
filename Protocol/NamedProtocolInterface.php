<?php

namespace Wizzaq\RestBundle\Protocol;

interface NamedProtocolInterface extends ProtocolInterface
{
    public function getProtocolName(): string;
}

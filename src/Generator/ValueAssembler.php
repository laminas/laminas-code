<?php

declare(strict_types=1);

namespace Laminas\Code\Generator;

interface ValueAssembler
{
    public function assemble(): string;
}

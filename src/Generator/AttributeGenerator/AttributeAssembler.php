<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

interface AttributeAssembler
{
    public function assemble(): string;
}

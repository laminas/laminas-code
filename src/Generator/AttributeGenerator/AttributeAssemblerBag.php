<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

final class AttributeAssemblerBag
{
    /**
     * @var AttributeAssembler[]
     */
    private array $assemblers = [];

    public function add(AttributeAssembler $assembler): void
    {
        $this->assemblers[] = $assembler;
    }

    public function toArray(): array
    {
        return $this->assemblers;
    }
}

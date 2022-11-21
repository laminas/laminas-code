<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

use Laminas\Code\Generator\AttributeGenerator\Exception\NotEmptyArgumentListException;
use ReflectionAttribute;

final class SimpleAttributeAssembler extends AbstractAttributeAssembler
{
    public function __construct(ReflectionAttribute $attributePrototype)
    {
        parent::__construct($attributePrototype);

        $this->assertAttributeWithoutArguments();
    }

    public function assemble(): string
    {
        $attributeName = $this->getName();

        return AttributePart::T_ATTR_START . $attributeName . AttributePart::T_ATTR_END;
    }

    private function assertAttributeWithoutArguments(): void
    {
        $arguments = $this->getArguments();

        if (!empty($arguments)) {
            throw new NotEmptyArgumentListException('Argument list has to be empty');
        }
    }
}

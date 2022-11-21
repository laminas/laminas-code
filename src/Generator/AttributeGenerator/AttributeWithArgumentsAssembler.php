<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

use Laminas\Code\Generator\AttributeGenerator\Exception\NestedAttributesAreNotSupportedException;

final class AttributeWithArgumentsAssembler extends AbstractAttributeAssembler
{
    public function assemble(): string
    {
        $attributeName = $this->getName();

        $attributeDefinition = AttributePart::T_ATTR_START . $attributeName . AttributePart::T_ATTR_ARGUMENTS_LIST_START;

        $this->generateArguments($attributeDefinition);

        return $attributeDefinition . AttributePart::T_ATTR_END;
    }

    private function generateArguments(string &$output): void
    {
        $argumentsList = [];

        foreach ($this->getArguments() as $argumentName => $argumentValue) {
            $argumentsList[] = $argumentName . AttributePart::T_ATTR_ARGUMENTS_LIST_ASSIGN_OPERAND . $this->formatArgumentValue($argumentValue);
        }

        $output .= implode(AttributePart::T_ATTR_ARGUMENTS_LIST_SEPARATOR, $argumentsList);
        $output .= AttributePart::T_ATTR_ARGUMENTS_LIST_END;
    }

    private function formatArgumentValue(mixed $argument): mixed
    {
        switch (true) {
            case is_string($argument):
                return "'$argument'";
            case is_bool($argument):
                return $argument ? 'true' : 'false';
            case is_array($argument):
                throw NestedAttributesAreNotSupportedException::create();
            default:
                return $argument;
        }
    }
}

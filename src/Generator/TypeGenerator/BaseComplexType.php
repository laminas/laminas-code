<?php

namespace Laminas\Code\Generator\TypeGenerator;

abstract class BaseComplexType implements Type
{
    /**
     * @param AtomicType[]|IntersectionType[] $types
     */
    final protected function __construct(protected array $types)
    {
    }

    /**
     * @return AtomicType[]|IntersectionType[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    abstract public static function getSeparator(): string;
}

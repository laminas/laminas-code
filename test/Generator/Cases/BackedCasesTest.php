<?php

namespace LaminasTest\Code\Generator\Cases;

use InvalidArgumentException;
use Laminas\Code\Generator\EnumGenerator\Cases\BackedCases;
use PHPUnit\Framework\TestCase;

final class BackedCasesTest extends TestCase
{
    public function testProvidingInvalidTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '"bool" is not a valid type for Enums, only "int" and "string" types are allowed.'
        );

        BackedCases::fromCasesWithType([], 'bool');
    }
}

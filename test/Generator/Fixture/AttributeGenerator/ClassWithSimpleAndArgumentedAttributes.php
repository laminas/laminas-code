<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\Fixture\AttributeGenerator;

#[AttributeWithArguments(stringArgument: 'any string', intArgument: 1, boolArgument: true)]
#[SimpleAttribute]
class ClassWithSimpleAndArgumentedAttributes
{
}

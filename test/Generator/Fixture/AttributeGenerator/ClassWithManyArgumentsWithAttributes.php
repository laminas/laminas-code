<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\Fixture\AttributeGenerator;

#[AttributeWithArguments(boolArgument: false, stringArgument: 'char chain', intArgument: 16)]
#[AttributeWithArguments]
class ClassWithManyArgumentsWithAttributes
{
}

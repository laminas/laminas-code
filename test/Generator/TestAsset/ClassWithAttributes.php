<?php

namespace LaminasTest\Code\Generator\TestAsset;

#[AttributeAsset]
#[AttributeAsset(stringArgument: 'any string', intArgument: 1)]
final class ClassWithAttributes
{
    #[AttributeAsset(stringArgument: 'on a property')]
    public string $property = '';

    #[AttributeAsset(stringArgument: 'on a method')]
    public function method(#[AttributeAsset] string $parameter = ''): void
    {
    }
}

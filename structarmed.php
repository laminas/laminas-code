<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->skip([
        'test/TestAsset',
        'test/*/TestAsset',
    ])
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('DeclareStatement', 'src/DeclareStatement.php')
    ->layer('Exception', 'src/Exception')
    ->layer('Generator', 'src/Generator', [
        'src/Generator/DocBlock',
        'src/Generator/EnumGenerator',
        'src/Generator/Exception',
        'src/Generator/TypeGenerator',
    ])
    ->layer('GeneratorDocBlock', 'src/Generator/DocBlock')
    ->layer('GeneratorEnum', 'src/Generator/EnumGenerator')
    ->layer('GeneratorException', 'src/Generator/Exception')
    ->layer('GeneratorType', 'src/Generator/TypeGenerator')
    ->layer('Generic', 'src/Generic')
    ->layer('Reflection', 'src/Reflection', [
        'src/Reflection/DocBlock',
        'src/Reflection/Exception',
    ])
    ->layer('ReflectionDocBlock', 'src/Reflection/DocBlock')
    ->layer('ReflectionException', 'src/Reflection/Exception')
    ->layer('Scanner', 'src/Scanner')
    ->ruleset([
        'Exception'           => [],
        'Scanner'             => [],
        'DeclareStatement'    => ['Exception'],
        'GeneratorException'  => ['Exception'],
        'ReflectionException' => ['Exception'],
        'Generic'             => ['+ReflectionException'],
        'ReflectionDocBlock'  => ['+Generic'],
        'Reflection'          => ['+ReflectionDocBlock', 'Scanner'],
        'GeneratorEnum'       => [],
        'GeneratorType'       => ['Generator', '+GeneratorException'],
        'GeneratorDocBlock'   => ['Generator', '+GeneratorException', '+ReflectionDocBlock'],
        'Generator'           => ['+DeclareStatement', '+GeneratorException', '+Reflection', 'GeneratorDocBlock', 'GeneratorType'],
    ]);

<?php

namespace LaminasTest\Code\Generator\TestAsset;

class TestClassWithManyProperties
{

    const FOO = 'foo';

    public static $fooStaticProperty = null;

    /**
     * @var bool
     */
    public $fooProperty = true;

    protected static $_barStaticProperty = 1;

    protected $_barProperty = 1.1115;

    private static string $_bazStaticProperty = self::FOO;

    private array $_bazProperty = [true, false, true];

    protected $_complexType = [
        5,
        'one' => 1,
        'two' => '2',
        [
            'bar',
            'baz',
            "\n"
        ]
    ];

}

<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

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

    private static $_bazStaticProperty = self::FOO;

    private $_bazProperty = [true, false, true];

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

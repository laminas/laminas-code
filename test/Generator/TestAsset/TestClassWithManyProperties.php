<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
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

    private $_bazProperty = array(true, false, true);

    protected $_complexType = array(
        5,
        'one' => 1,
        'two' => '2',
        array(
            'bar',
            'baz',
            //PHP_EOL
        )
    );

}

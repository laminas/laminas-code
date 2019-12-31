<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\TestAsset;

/***
 * /!\ Don't fix this file with the coding style.
 * The class Laminas\Code\Reflection\FunctionReflection must parse a lot of closure formats
 */

function function1()
{
    return 'function1';
}


/**
 * Laminas Function Two
 *
 * This is the long description for funciton two
 *
 * @param string $one
 * @param string $two
 * @return string
 */
function function2($one, $two = 'two')
{
    return 'blah';
}


/**
 * Enter description here...
 *
 * @param string $one
 * @param int $two
 * @return true
 */
function function3($one, $two = 2)
{
    return true;
}

function function4($arg) {
    return 'function4';
}

function function5() { return 'function5'; }

function function6()
{
    $closure = function() { return 'bar'; };
    return 'function6';
}

$foo = 'foo'; function function7() { return 'function7'; }

function function8() { return 'function8'; } function function9() { return 'function9'; }

function function10() { $closure = function() { return 'function10'; }; return $closure(); } function function11() { return 'function11'; }

function function12() {}

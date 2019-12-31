<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\TestAsset;

function function1()
{
    return 'foo';
}


/**
 * Laminas Function Two
 *
 * This is the long description for funciton two
 *
 * @param unknown_type $one
 * @param unknown_type $two
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
function function6($one, $two = 2)
{
    return true;
}

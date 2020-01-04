<?php
/**
 * @author pifeifei <pifeifei1989@qq.com>
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\TestAsset;

/**
 * fun document
 *
 * @param $param
 * @param bool $param2
 * @return string
 */
function foo_bar($param, $param2=true)
{
    if(true){$abc='default+';}
    $abc.=$param.$param2."abc'\"";
    $fun=function(){};
    return $abc;
}

function bar_foo()
{
    $x = 5 + 5;
    $y = 'this string';
}

function four($one, $two = array([array('default')]))
{
    // four
}

function five(\ZendTest\Code\TestAsset\AbstractClass $a)
{
    // five
}
<?php

/**
 * Enter description here...
 *
 *
 */
function someFunction()
{
    /* test test */
}

/**
 * someFunctionMultiLines
 * Enter description here...
 */
function someFunctionMultiLines()
{
    /* test test */

    /* test test */

    /* test test */
}

/**
 * Enter description here...
 *
 * @return bool
 */
function withParamsAndReturnType(
    $mixed,
    array $array,
    callable $callable = null,
    ?int $int = 0
): bool {
    /* test test */
    return true;
}

function & byRefReturn()
{
    $foo = 'bar';

    return $foo;
}
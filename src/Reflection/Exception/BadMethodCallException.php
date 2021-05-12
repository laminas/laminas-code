<?php

namespace Laminas\Code\Reflection\Exception;

use Laminas\Code\Exception;

class BadMethodCallException extends Exception\BadMethodCallException implements
    ExceptionInterface
{
}

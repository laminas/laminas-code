<?php
declare(strict_types=1);

namespace TestNamespace;

enum Flags: int
{
    case Public = 1;
    case Protected = 2;
    case Private = 3;
}

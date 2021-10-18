<?php
declare(strict_types=1);

namespace TestNamespace;

enum Orientation: string
{
    case North = 'N';
    case South = 'S';
    case East = 'E';
    case West = 'W';
}

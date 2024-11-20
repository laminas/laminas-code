<?php

namespace Laminas\Code\Reflection\DocBlock\Tag;

use Stringable;

use function explode;
use function preg_match;
use function rtrim;

class MethodTag implements TagInterface, PhpDocTypedTagInterface, Stringable
{
    /** @var list<string> */
    protected $types = [];

    /** @var string|null */
    protected $methodName;

    /** @var string|null */
    protected $description;

    /** @var bool */
    protected $isStatic = false;

    /** @return 'method' */
    public function getName()
    {
        return 'method';
    }

    /** @inheritDoc */
    public function initialize($content)
    {
        $match = [];

        if (! preg_match('#^(static[\s]+)?(.+[\s]+)?(.+\(\))[\s]*(.*)$#m', $content, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->isStatic = true;
        }

        if ($match[2] !== '') {
            $this->types = explode('|', rtrim($match[2]));
        }

        $this->methodName = $match[3];

        if ($match[4] !== '') {
            $this->description = $match[4];
        }
    }

    /**
     * Get return value type
     *
     * @deprecated 2.0.4 use getTypes instead
     *
     * @return null|string
     */
    public function getReturnType()
    {
        if (empty($this->types)) {
            return null;
        }

        return $this->types[0];
    }

    /** @inheritDoc */
    public function getTypes()
    {
        return $this->types;
    }

    /** @return string|null */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /** @return string|null */
    public function getDescription()
    {
        return $this->description;
    }

    /** @return bool */
    public function isStatic()
    {
        return $this->isStatic;
    }

    /** @return non-empty-string */
    public function __toString(): string
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . "\n";
    }
}

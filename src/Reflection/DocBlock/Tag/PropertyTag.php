<?php

namespace Laminas\Code\Reflection\DocBlock\Tag;

use Stringable;

use function explode;
use function preg_match;
use function rtrim;

class PropertyTag implements TagInterface, PhpDocTypedTagInterface, Stringable
{
    /** @var list<string> */
    protected $types = [];

    /** @var string|null */
    protected $propertyName;

    /** @var string|null */
    protected $description;

    /**
     * @return string
     */
    public function getName()
    {
        return 'property';
    }

    /** @inheritDoc */
    public function initialize($content)
    {
        $match = [];
        if (! preg_match('#^(.+)?(\$[\S]+)[\s]*(.*)$#m', $content, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->types = explode('|', rtrim($match[1]));
        }

        if ($match[2] !== '') {
            $this->propertyName = $match[2];
        }

        if ($match[3] !== '') {
            $this->description = $match[3];
        }
    }

    /**
     * @deprecated 2.0.4 use getTypes instead
     *
     * @return null|string
     */
    public function getType()
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

    /**
     * @return null|string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . "\n";
    }
}

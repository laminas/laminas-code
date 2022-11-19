<?php

namespace Laminas\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Generic\Prototype\PrototypeGenericInterface;

use function explode;
use function trim;

class GenericTag implements TagInterface, PrototypeGenericInterface
{
    /** @var string|null */
    protected $name;

    /** @var string|null */
    protected $content;

    /** @var string */
    protected $contentSplitCharacter;

    /** @var list<string> */
    protected $values = [];

    /**
     * @param string $contentSplitCharacter
     */
    public function __construct($contentSplitCharacter = ' ')
    {
        $this->contentSplitCharacter = $contentSplitCharacter;
    }

    /** @inheritDoc */
    public function initialize($content)
    {
        $this->parse($content);
    }

    /** @return string|null */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /** @return string|null */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param  int $position
     * @return string
     */
    public function returnValue($position)
    {
        return $this->values[$position];
    }

    /** @return non-empty-string */
    public function __toString()
    {
        return 'DocBlock Tag [ * @' . $this->name . ' ]' . "\n";
    }

    /**
     * @param  string $docBlockLine
     * @return void
     */
    protected function parse($docBlockLine)
    {
        $this->content = trim($docBlockLine);
        $this->values  = explode($this->contentSplitCharacter, $docBlockLine);
    }
}

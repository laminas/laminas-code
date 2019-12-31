<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Generic\Prototype\PrototypeGenericInterface;

class GenericTag implements TagInterface, PrototypeGenericInterface
{
    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $content = null;

    /**
     * @var null|string
     */
    protected $contentSplitCharacter = null;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @param  string $contentSplitCharacter
     */
    public function __construct($contentSplitCharacter = ' ')
    {
        $this->contentSplitCharacter = $contentSplitCharacter;
    }

    /**
     * @param  string $tagDocBlockLine
     * @return void
     */
    public function initialize($tagDocBlockLine)
    {
        $this->parse($tagDocBlockLine);
    }

    /**
     * Get annotation tag name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
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

    /**
     * Serialize to string
     *
     * Required by Reflector
     *
     * @todo   What should this do?
     * @return string
     */
    public function __toString()
    {
        return 'DocBlock Tag [ * @' . $this->name . ' ]' . PHP_EOL;
    }

    /**
     * @param  string $docBlockLine
     */
    protected function parse($docBlockLine)
    {
        $this->content = trim($docBlockLine);
        $this->values = explode($this->contentSplitCharacter, $docBlockLine);
    }
}

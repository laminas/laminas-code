<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection\DocBlock\Tag;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 */
class PropertyTag implements TagInterface
{
    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $propertyName = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @return string
     */
    public function getName()
    {
        return 'property';
    }

    /**
     * Initializer
     *
     * @param string $tagDocBlockLine
     */
    public function initialize($tagDocblockLine)
    {
        if (preg_match('#^(.+)?(\$[\S]+)[\s]*(.*)$#m', $tagDocblockLine, $match)) {
            if ($match[1] !== '') {
                $this->type = rtrim($match[1]);
            }

            if ($match[2] !== '') {
                $this->propertyName = $match[2];
            }

            if ($match[3] !== '') {
                $this->description = $match[3];
            }
        }
    }

    /**
     * Get property variable type
     *
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get property name
     *
     * @return null|string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Get property description
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function __toString()
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . PHP_EOL;
    }
}

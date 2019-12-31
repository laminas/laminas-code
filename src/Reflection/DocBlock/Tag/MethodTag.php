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
class MethodTag implements TagInterface
{
    /**
     * Return value type
     *
     * @var string
     */
    protected $returnType = null;

    /**
     * Method name
     *
     * @var string
     */
    protected $methodName = null;

    /**
     * Description
     *
     * @var string
     */
    protected $description = null;

    /**
     * Is static method
     *
     * @var bool
     */
    protected $isStatic = false;

    /**
     * Get tag name
     *
     * @return string
     */
    public function getName()
    {
        return 'method';
    }

    /**
     * Initializer
     *
     * @param string $tagDocblockLine
     */
    public function initialize($tagDocblockLine)
    {
        if (preg_match('#^(static[\s]+)?(.+[\s]+)?(.+\(\))[\s]*(.*)$#m', $tagDocblockLine, $match)) {
            if ($match[1] !== '') {
                $this->isStatic = true;
            }

            if ($match[2] !== '') {
                $this->returnType = rtrim($match[2]);
            }

            $this->methodName = $match[3];

            if ($match[4] !== '') {
                $this->description = $match[4];
            }
        }
    }

    /**
     * Get return value type
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Get method name
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Get method description
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Is method static
     *
     * @return bool
     */
    public function isStatic()
    {
        return $this->isStatic;
    }

    public function __toString()
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . PHP_EOL;
    }
}

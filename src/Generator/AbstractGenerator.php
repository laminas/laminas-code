<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator;

use Traversable;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 */
abstract class AbstractGenerator implements GeneratorInterface
{

    /**
     * Line feed to use in place of EOL
     *
     */
    const LINE_FEED = "\n";

    /**
     * @var bool
     */
    protected $isSourceDirty = true;

    /**
     * @var int|string 4 spaces by default
     */
    protected $indentation = '    ';

    /**
     * @var string
     */
    protected $sourceContent = null;

    /**
     * setSourceDirty()
     *
     * @param bool $isSourceDirty
     * @return AbstractGenerator
     */
    public function setSourceDirty($isSourceDirty = true)
    {
        $this->isSourceDirty = ($isSourceDirty) ? true : false;
        return $this;
    }

    /**
     * isSourceDirty()
     *
     * @return bool
     */
    public function isSourceDirty()
    {
        return $this->isSourceDirty;
    }

    /**
     * setIndentation()
     *
     * @param string|int $indentation
     * @return AbstractGenerator
     */
    public function setIndentation($indentation)
    {
        $this->indentation = $indentation;
        return $this;
    }

    /**
     * getIndentation()
     *
     * @return string|int
     */
    public function getIndentation()
    {
        return $this->indentation;
    }

    /**
     * setSourceContent()
     *
     * @param string $sourceContent
     * @return AbstractGenerator
     */
    public function setSourceContent($sourceContent)
    {
        $this->sourceContent = $sourceContent;
        return $this;
    }

    /**
     * getSourceContent()
     *
     * @return string
     */
    public function getSourceContent()
    {
        return $this->sourceContent;
    }

    /**
     * setOptions()
     *
     * @param array|Traversable $options
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s expects an array or Traversable object; received "%s"',
                    __METHOD__,
                    (is_object($options) ? get_class($options) : gettype($options))
                ));
        }

        foreach ($options as $optionName => $optionValue) {
            $methodName = 'set' . $optionName;
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($optionValue);
            }
        }

        return $this;
    }
}

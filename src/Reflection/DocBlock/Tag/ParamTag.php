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
class ParamTag implements TagInterface
{
    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $variableName = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @return string
     */
    public function getName()
    {
        return 'param';
    }

    /**
     * Initializer
     *
     * @param string $tagDocBlockLine
     */
    public function initialize($tagDocBlockLine)
    {
        $matches = array();
        preg_match('#([\w|\\\]+)(?:\s+(\$\S+)){0,1}(?:\s+(.*))?#s', $tagDocBlockLine, $matches);

        $this->type = $matches[1];

        if (isset($matches[2])) {
            $this->variableName = $matches[2];
        }

        if (isset($matches[3])) {
            $this->description = trim(preg_replace('#\s+#', ' ', $matches[3]));
        }
    }

    /**
     * Get parameter variable type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get parameter name
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    public function getDescription()
    {
        return $this->description;
    }
}

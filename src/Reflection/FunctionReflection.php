<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection;

use Laminas\Code\Reflection\DocBlock\Tag\ReturnTag;
use ReflectionFunction;

class FunctionReflection extends ReflectionFunction implements ReflectionInterface
{
    /**
     * Get function DocBlock
     *
     * @throws Exception\InvalidArgumentException
     * @return DocBlockReflection
     */
    public function getDocBlock()
    {
        if ('' == ($comment = $this->getDocComment())) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s does not have a DocBlock',
                $this->getName()
            ));
        }

        $instance = new DocBlockReflection($comment);

        return $instance;
    }

    /**
     * Get start line (position) of function
     *
     * @param  bool $includeDocComment
     * @return int
     */
    public function getStartLine($includeDocComment = false)
    {
        if ($includeDocComment) {
            if ($this->getDocComment() != '') {
                return $this->getDocBlock()->getStartLine();
            }
        }

        return parent::getStartLine();
    }

    /**
     * Get contents of function
     *
     * @param  bool $includeDocBlock
     * @return string
     */
    public function getContents($includeDocBlock = true)
    {
        return implode("\n",
            array_splice(
                file($this->getFileName()),
                $this->getStartLine($includeDocBlock),
                ($this->getEndLine() - $this->getStartLine()),
                true
            )
        );
    }

    /**
     * Get function parameters
     *
     * @return ParameterReflection[]
     */
    public function getParameters()
    {
        $phpReflections  = parent::getParameters();
        $laminasReflections = array();
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance          = new ParameterReflection($this->getName(), $phpReflection->getName());
            $laminasReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);

        return $laminasReflections;
    }

    /**
     * Get return type tag
     *
     * @throws Exception\InvalidArgumentException
     * @return ReturnTag
     */
    public function getReturn()
    {
        $docBlock = $this->getDocBlock();
        if (!$docBlock->hasTag('return')) {
            throw new Exception\InvalidArgumentException(
                'Function does not specify an @return annotation tag; cannot determine return type'
            );
        }

        $tag    = $docBlock->getTag('return');
        return new DocBlockReflection('@return ' . $tag->getDescription());
    }

    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Required due to bug in php
     *
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }
}

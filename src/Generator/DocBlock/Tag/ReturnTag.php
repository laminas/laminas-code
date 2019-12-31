<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionDocBlockTag;

class ReturnTag extends Tag
{
    /**
     * @var string
     */
    protected $datatype = null;

    /**
     * @param  ReflectionDocBlockTag $reflectionTagReturn
     * @return ReturnTag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTagReturn)
    {
        $returnTag = new static();
        $returnTag
            ->setName('return')
            ->setDatatype($reflectionTagReturn->getType()) // @todo rename
            ->setDescription($reflectionTagReturn->getDescription());

        return $returnTag;
    }

    /**
     * @param  string $datatype
     * @return ReturnTag
     */
    public function setDatatype($datatype)
    {
        $this->datatype = $datatype;
        return $this;
    }

    /**
     * @return string
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '@return ' . $this->datatype . ' ' . $this->description;
    }
}

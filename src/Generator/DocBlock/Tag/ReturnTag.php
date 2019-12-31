<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionDocBlockTag;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 */
class ReturnTag extends Tag
{

    /**
     * @var string
     */
    protected $datatype = null;

    /**
     * fromReflection()
     *
     * @param ReflectionDocBlockTag $reflectionTagReturn
     * @return ReturnTag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTagReturn)
    {
        $returnTag = new self();

        $returnTag->setName('return');
        $returnTag->setDatatype($reflectionTagReturn->getType()); // @todo rename
        $returnTag->setDescription($reflectionTagReturn->getDescription());

        return $returnTag;
    }

    /**
     * setDatatype()
     *
     * @param string $datatype
     * @return ReturnTag
     */
    public function setDatatype($datatype)
    {
        $this->datatype = $datatype;
        return $this;
    }

    /**
     * getDatatype()
     *
     * @return string
     */
    public function getDatatype()
    {
        return $this->datatype;
    }


    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@return ' . $this->datatype . ' ' . $this->description;
        return $output;
    }

}

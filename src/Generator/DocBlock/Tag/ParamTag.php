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
class ParamTag extends Tag
{

    /**
     * @var string
     */
    protected $datatype = null;

    /**
     * @var string
     */
    protected $paramName = null;

    /**
     * fromReflection()
     *
     * @param ReflectionDocBlockTag $reflectionTagParam
     * @return ParamTag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTagParam)
    {
        $paramTag = new self();

        $paramTag->setName('param');
        $paramTag->setDatatype($reflectionTagParam->getType()); // @todo rename
        $paramTag->setParamName($reflectionTagParam->getVariableName());
        $paramTag->setDescription($reflectionTagParam->getDescription());

        return $paramTag;
    }

    /**
     * setDatatype()
     *
     * @param string $datatype
     * @return ParamTag
     */
    public function setDatatype($datatype)
    {
        $this->datatype = $datatype;
        return $this;
    }

    /**
     * getDatatype
     *
     * @return string
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * setParamName()
     *
     * @param string $paramName
     * @return ParamTag
     */
    public function setParamName($paramName)
    {
        $this->paramName = $paramName;
        return $this;
    }

    /**
     * getParamName()
     *
     * @return string
     */
    public function getParamName()
    {
        return $this->paramName;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@param '
            . (($this->datatype != null) ? $this->datatype : 'unknown')
            . (($this->paramName != null) ? ' $' . $this->paramName : '')
            . (($this->description != null) ? ' ' . $this->description : '');
        return $output;
    }

}

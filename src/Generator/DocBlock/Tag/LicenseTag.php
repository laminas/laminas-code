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
class LicenseTag extends Tag
{
    /**
     * @var string
     */
    protected $url = null;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (isset($options['url'])) {
            $this->setUrl($options['url']);
        }

        if (empty($this->name)) {
            $this->setName('license');
        }
    }

    /**
     * fromReflection()
     *
     * @param ReflectionDocBlockTag $reflectionTagLicense
     * @return LicenseTag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTagLicense)
    {
        $returnTag = new self();

        $returnTag->setName('license');
        $returnTag->setUrl($reflectionTagLicense->getUrl());
        $returnTag->setDescription($reflectionTagLicense->getDescription());

        return $returnTag;
    }

    /**
     * setUrl()
     *
     * @param string $url
     * @return LicenseTag
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * getUrl()
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@' . $this->name
                . (($this->url !== null) ? ' ' . $this->url : '')
                . (($this->description !== null) ? ' ' . $this->description : '');
        return $output;
    }
}

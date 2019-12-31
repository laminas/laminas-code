<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionDocBlockTag;

class LicenseTag extends Tag
{
    /**
     * @var string
     */
    protected $url = null;

    /**
     * @var string
     */
    protected $licenseName = null;

    /**
     * @param  array $options
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
     * @param  ReflectionDocBlockTag $reflectionTagLicense
     * @return LicenseTag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTagLicense)
    {
        $licenseTag = new static();
        $licenseTag
            ->setName('license')
            ->setUrl($reflectionTagLicense->getUrl())
            ->setLicenseName($reflectionTagLicense->getDescription());

        return $licenseTag;
    }

    /**
     * @param  string $url
     * @return LicenseTag
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param  string $name
     * @return LicenseTag
     */
    public function setLicenseName($name)
    {
        $this->licenseName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLicenseName()
    {
        return $this->licenseName;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@license '
            . (($this->url != null) ? $this->url : 'unknown')
            . (($this->licenseName != null) ? ' ' . $this->licenseName : '')
            . (($this->description != null) ? ' ' . $this->description : '');

        return $output;
    }
}

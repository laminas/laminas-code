<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator;

/**
 * @category   Laminas
 * @package    Laminas_Code_Generator
 */
class BodyGenerator extends AbstractGenerator
{

    /**
     * @var string
     */
    protected $content = null;

    /**
     * setContent()
     *
     * @param string $content
     * @return BodyGenerator
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * getContent()
     *
     * @return string
     */
    public function getContent()
    {
        return (string) $this->content;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        return $this->getContent();
    }
}

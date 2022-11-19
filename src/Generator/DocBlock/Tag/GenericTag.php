<?php

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\AbstractGenerator;
use Laminas\Code\Generic\Prototype\PrototypeGenericInterface;

use function ltrim;

class GenericTag extends AbstractGenerator implements TagInterface, PrototypeGenericInterface
{
    /** @var string|null */
    protected $name;

    /** @var string|null */
    protected $content;

    /**
     * @param string|null $name
     * @param string|null $content
     */
    public function __construct($name = null, $content = null)
    {
        if (! empty($name)) {
            $this->setName($name);
        }

        if (! empty($content)) {
            $this->setContent($content);
        }
    }

    /**
     * @param  string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = ltrim($name, '@');
        return $this;
    }

    /** @return string|null */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /** @return string|null */
    public function getContent()
    {
        return $this->content;
    }

    /** @return non-empty-string */
    public function generate()
    {
        return '@' . $this->name
            . (! empty($this->content) ? ' ' . $this->content : '');
    }
}

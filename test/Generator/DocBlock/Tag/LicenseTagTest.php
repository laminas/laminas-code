<?php

namespace LaminasTest\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\Tag\LicenseTag;
use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlockReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class LicenseTagTest extends TestCase
{
    /** @var LicenseTag */
    protected $tag;

    /** @var TagManager */
    protected $tagmanager;

    protected function setUp(): void
    {
        $this->tag        = new LicenseTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    protected function tearDown(): void
    {
        $this->tag        = null;
        $this->tagmanager = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setUrl('foo');
        $this->tag->setLicenseName('bar');

        self::assertSame('foo', $this->tag->getUrl());
        self::assertSame('bar', $this->tag->getLicenseName());
    }

    public function testNameIsCorrect()
    {
        self::assertSame('license', $this->tag->getName());
    }

    public function testLicenseProducesCorrectDocBlockLine()
    {
        $this->tag->setUrl('foo');
        $this->tag->setLicenseName('bar bar bar');
        self::assertSame('@license foo bar bar bar', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'url'         => 'foo',
            'licenseName' => 'bar',
        ]);
        $tagWithOptionsFromConstructor = new LicenseTag('foo', 'bar');
        self::assertSame($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @license http://zend.com License');
        $reflectionTag = $docreflection->getTag('license');

        /** @var LicenseTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(LicenseTag::class, $tag);
        self::assertSame('http://zend.com', $tag->getUrl());
        self::assertSame('License', $tag->getLicenseName());
    }
}

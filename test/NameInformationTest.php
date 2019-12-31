<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code;

use Laminas\Code\NameInformation;

class NameInformationTest extends \PHPUnit_Framework_TestCase
{
    public function testNamespaceResolverPersistsNamespace()
    {
        $nr = new NameInformation('Foo\Bar');
        $this->assertEquals('Foo\Bar', $nr->getNamespace());

        $nr = new NameInformation();
        $nr->setNamespace('Bar\Baz');
        $this->assertEquals('Bar\Baz', $nr->getNamespace());
    }

    public function testNamespaceResolverPersistsUseRules()
    {
        $nr = new NameInformation('Foo\Bar', ['Aaa\Bbb\Ccc' => 'C']);
        $this->assertEquals(['Aaa\Bbb\Ccc' => 'C'], $nr->getUses());

        $nr = new NameInformation();
        $nr->setUses(['Aaa\Bbb\Ccc']);
        $this->assertEquals(['Aaa\Bbb\Ccc' => 'Ccc'], $nr->getUses());

        $nr->setUses(['ArrayObject']);
        $this->assertEquals(['ArrayObject' => 'ArrayObject'], $nr->getUses());

        $nr->setUses(['ArrayObject' => 'AO']);
        $this->assertEquals(['ArrayObject' => 'AO'], $nr->getUses());

        $nr->setUses(['\Aaa\Bbb\Ccc' => 'Ccc']);
        $this->assertEquals(['Aaa\Bbb\Ccc' => 'Ccc'], $nr->getUses());
    }

    public function testNamespaceResolverCorrectlyResolvesNames()
    {
        $nr = new NameInformation;
        $nr->setNamespace('Laminas\MagicComponent');
        $nr->setUses([
            'ArrayObject',
            'Laminas\OtherMagicComponent\Foo',
            'Laminas\SuperMagic' => 'SM',
        ]);

        // test against namespace
        $this->assertEquals('Laminas\MagicComponent\Bar', $nr->resolveName('Bar'));

        // test against uses
        $this->assertEquals('ArrayObject', $nr->resolveName('ArrayObject'));
        $this->assertEquals('ArrayObject', $nr->resolveName('\ArrayObject'));
        $this->assertEquals('Laminas\OtherMagicComponent\Foo', $nr->resolveName('Foo'));
        $this->assertEquals('Laminas\SuperMagic', $nr->resolveName('SM'));
        $this->assertEquals('Laminas\SuperMagic\Bar', $nr->resolveName('SM\Bar'));
    }
}

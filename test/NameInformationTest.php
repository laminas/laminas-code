<?php

/**
 * @see       https://github.com/laminasframwork/laminas-code for the canonical source repository
 * @copyright https://github.com/laminasframwork/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminasframwork/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code;

use Laminas\Code\NameInformation;
use PHPUnit\Framework\TestCase;

class NameInformationTest extends TestCase
{
    public function testNamespaceResolverPersistsNamespace()
    {
        $nr = new NameInformation('Foo\Bar');
        self::assertEquals('Foo\Bar', $nr->getNamespace());

        $nr = new NameInformation();
        $nr->setNamespace('Bar\Baz');
        self::assertEquals('Bar\Baz', $nr->getNamespace());
    }

    public function testNamespaceResolverPersistsUseRules()
    {
        $nr = new NameInformation('Foo\Bar', ['Aaa\Bbb\Ccc' => 'C']);
        self::assertEquals(['Aaa\Bbb\Ccc' => 'C'], $nr->getUses());

        $nr = new NameInformation();
        $nr->setUses(['Aaa\Bbb\Ccc']);
        self::assertEquals(['Aaa\Bbb\Ccc' => 'Ccc'], $nr->getUses());

        $nr->setUses(['ArrayObject']);
        self::assertEquals(['ArrayObject' => 'ArrayObject'], $nr->getUses());

        $nr->setUses(['ArrayObject' => 'AO']);
        self::assertEquals(['ArrayObject' => 'AO'], $nr->getUses());

        $nr->setUses(['\Aaa\Bbb\Ccc' => 'Ccc']);
        self::assertEquals(['Aaa\Bbb\Ccc' => 'Ccc'], $nr->getUses());
    }

    public function testNamespaceResolverCorrectlyResolvesNames()
    {
        $nr = new NameInformation();
        $nr->setNamespace('Laminas\MagicComponent');
        $nr->setUses([
            'ArrayObject',
            'Laminas\OtherMagicComponent\Foo',
            'Laminas\SuperMagic' => 'SM',
        ]);

        // test against namespace
        self::assertEquals('Laminas\MagicComponent\Bar', $nr->resolveName('Bar'));

        // test against uses
        self::assertEquals('ArrayObject', $nr->resolveName('ArrayObject'));
        self::assertEquals('ArrayObject', $nr->resolveName('\ArrayObject'));
        self::assertEquals('Laminas\OtherMagicComponent\Foo', $nr->resolveName('Foo'));
        self::assertEquals('Laminas\SuperMagic', $nr->resolveName('SM'));
        self::assertEquals('Laminas\SuperMagic\Bar', $nr->resolveName('SM\Bar'));
    }
}

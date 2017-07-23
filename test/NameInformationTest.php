<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code;

use PHPUnit\Framework\TestCase;
use Zend\Code\NameInformation;

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
        $nr->setNamespace('Zend\MagicComponent');
        $nr->setUses([
            'ArrayObject',
            'Zend\OtherMagicComponent\Foo',
            'Zend\SuperMagic' => 'SM',
        ]);

        // test against namespace
        self::assertEquals('Zend\MagicComponent\Bar', $nr->resolveName('Bar'));

        // test against uses
        self::assertEquals('ArrayObject', $nr->resolveName('ArrayObject'));
        self::assertEquals('ArrayObject', $nr->resolveName('\ArrayObject'));
        self::assertEquals('Zend\OtherMagicComponent\Foo', $nr->resolveName('Foo'));
        self::assertEquals('Zend\SuperMagic', $nr->resolveName('SM'));
        self::assertEquals('Zend\SuperMagic\Bar', $nr->resolveName('SM\Bar'));
    }
}

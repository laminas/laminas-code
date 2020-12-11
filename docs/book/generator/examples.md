# Laminas\\Code\\Generator Examples

## Generating PHP classes

The following example generates an empty class with a class-level DocBlock.

```php
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;

$foo      = new ClassGenerator();
$docblock = DocBlockGenerator::fromArray([
    'shortDescription' => 'Sample generated class',
    'longDescription'  => 'This is a class generated with Laminas\Code\Generator.',
    'tags'             => [
        [
            'name'        => 'version',
            'description' => '$Rev:$',
        ],
        [
            'name'        => 'license',
            'description' => 'New BSD',
        ],
    ],
]);
$foo->setName('Foo')
    ->setDocblock($docblock);
echo $foo->generate();
```

The above code will result in the following:

```php
/**
 * Sample generated class
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @version $Rev:$
 * @license New BSD
 *
 */
class Foo
{

}
```

### Generating PHP classes with class properties

Building on the previous example, we now add properties to our generated class.

```php
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\PropertyGenerator;

$foo      = new ClassGenerator();
$docblock = DocBlockGenerator::fromArray([
    'shortDescription' => 'Sample generated class',
    'longDescription'  => 'This is a class generated with Laminas\Code\Generator.',
    'tags'             => [
        [
            'name'        => 'version',
            'description' => '$Rev:$',
        ],
        [
            'name'        => 'license',
            'description' => 'New BSD',
        ],
    ],
]);
$foo->setName('Foo')
    ->setDocblock($docblock)
    ->addProperties([
         ['bar', 'baz', PropertyGenerator::FLAG_PROTECTED],
         ['baz', 'bat', PropertyGenerator::FLAG_PUBLIC]
   ])
   ->addConstants([
         ['bat',  'foobarbazbat']
    ]);
echo $foo->generate();
```

The above results in the following class definition:

```php
/**
 * Sample generated class
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @version $Rev:$
 * @license New BSD
 *
 */
class Foo
{

    protected $bar = 'baz';

    public $baz = 'bat';

    const bat = 'foobarbazbat';

}
```

### Generating PHP classes with class methods

`Laminas\Code\Generator\ClassGenerator` allows you to attach methods with optional content to your
classes. Methods may be attached as either arrays or concrete `Laminas\Code\Generator\MethodGenerator`
instances.

```php
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;

$foo      = new ClassGenerator();
$docblock = DocBlockGenerator::fromArray([
    'shortDescription' => 'Sample generated class',
    'longDescription'  => 'This is a class generated with Laminas\Code\Generator.',
    'tags'             => [
        [
            'name'        => 'version',
            'description' => '$Rev:$',
        ],
        [
            'name'        => 'license',
            'description' => 'New BSD',
        ],
    ],
]);
$foo->setName('Foo')
    ->setDocblock($docblock)
    ->addProperties([
        ['bar',  'baz',          PropertyGenerator::FLAG_PROTECTED],
        ['baz',  'bat',          PropertyGenerator::FLAG_PUBLIC]
    ])
    ->addConstants([
        ['bat',  'foobarbazbat', PropertyGenerator::FLAG_CONSTANT]
    ])
    ->addMethods([
        // Method passed as array
        MethodGenerator::fromArray([
            'name'       => 'setBar',
            'parameters' => ['bar'],
            'body'       => '$this->bar = $bar;' . "\n" . 'return $this;',
            'docblock'   => DocBlockGenerator::fromArray([
                'shortDescription' => 'Set the bar property',
                'longDescription'  => null,
                'tags'             => [
                     new Tag\ParamTag(
                        'bar', 
                        [
                            'string',
                            'array'
                        ],
                        'parameter description'
                        )
                    new Tag\ReturnTag([
                        'datatype'  => 'string',
                    ]),
                ],
            ]),
        ]),
        // Method passed as concrete instance
        new MethodGenerator(
            'getBar',
            [],
            MethodGenerator::FLAG_PUBLIC,
            'return $this->bar;',
            DocBlockGenerator::fromArray([
                'shortDescription' => 'Retrieve the bar property',
                'longDescription'  => null,
                'tags'             => [
                    new Tag\ReturnTag([
                        'datatype'  => 'string|null',
                    ]),
                ],
            ])
        ),
    ]);

echo $foo->generate();
```

The above generates the following output:

```php
/**
 * Sample generated class
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @version $Rev:$
 * @license New BSD
 */
class Foo
{

    protected $bar = 'baz';

    public $baz = 'bat';

    const bat = 'foobarbazbat';

    /**
     * Set the bar property
     *
     * @param string|array $bar parameter description
     */
    public function setBar($bar)
    {
        $this->bar = $bar;
        return $this;
    }

    /**
     * Retrieve the bar property
     *
     * @return string|null
     */
    public function getBar()
    {
        return $this->bar;
    }

}
```

## Generating PHP files

`Laminas\Code\Generator\FileGenerator` can be used to generate the contents of a *PHP* file. You can
include classes as well as arbitrary content body. When attaching classes, you should attach either
concrete `Laminas\Code\Generator\ClassGenerator` instances or an array defining the class.

In the example below, we will assume you've defined `$foo` per one of the class definitions in a
previous example.

```php
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\FileGenerator;

$file = FileGenerator::fromArray([
    'classes'  => [$foo],
    'docblock' => DocBlockGenerator::fromArray([
        'shortDescription' => 'Foo class file',
        'longDescription'   => null,
        'tags'             => [
            [
                'name'        => 'license',
                'description' => 'New BSD',
            ],
        ],
    ]),
    'body'     => 'define(\'APPLICATION_ENV\', \'testing\');',
]);
```

Calling `generate()` will generate the code -- but not write it to a file. You will need to capture
the contents and write them to a file yourself. As an example:

```php
$code = $file->generate();
file_put_contents('Foo.php', $code);
```

The above will generate the following file:

```php
<?php
/**
 * Foo class file
 *
 * @license New BSD
 */

/**
 * Sample generated class
 *
 * This is a class generated with Laminas\Code\Generator.
 *
 * @version $Rev:$
 * @license New BSD
 */
class Foo
{

    protected $bar = 'baz';

    public $baz = 'bat';

    const bat = 'foobarbazbat';

    /**
     * Set the bar property
     *
     * @param string bar
     * @return string
     */
    public function setBar($bar)
    {
        $this->bar = $bar;
        return $this;
    }

    /**
     * Retrieve the bar property
     *
     * @return string|null
     */
    public function getBar()
    {
        return $this->bar;
    }

}

define('APPLICATION_ENV', 'testing');
```

## Add code to existing PHP files and classes

### Seeding PHP class generation via reflection

You may add code to an existing class. To do so, first use the static `fromReflection()` method to
map the class into a generator object. From there, you may add additional properties or methods, and
then regenerate the class.

```php
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\ClassReflection;

$generator = ClassGenerator::fromReflection(
    new ClassReflection($class)
);
$generator->addMethod(
    'setBaz',
    ['baz'],
    MethodGenerator::FLAG_PUBLIC,
    '$this->baz = $baz;' . "\n" . 'return $this;',
    DocBlockGenerator::fromArray([
        'shortDescription' => 'Set the baz property',
        'longDescription'  => null,
        'tags'             => [
            new Tag\ParamTag([
                'paramName' => 'baz',
                'datatype'  => 'string'
            ]),
            new Tag\ReturnTag([
                'datatype'  => 'string',
            ]),
        ],
    ])
);
$code = $generator->generate();
```

# Upgrade

All changes required for users to upgrade to newer versions of this library will be documented or referenced here.

## 2.* -> 3.0.0

### `string`, `int`, `float`, `bool` are no longer ignored

In 2.x, a `Zend\Code\Generator\ParameterGenerator` with name `foo` and type 
`string`, `int`, `float` or `bool` simply generated code `"$foo"`:

```php
$generator = new \Zend\Code\ParameterGenerator('foo');

$generator->setType('string');

echo $generator->generate(); // "$foo"
```

In 3.x, this code will instead produce `"string $foo"`.
If you generate code that should run in PHP 5.x, it is advisable to strip
`string`, `int`, `float` and `bool` from type definitions passed to
`Zend\Code\ParameterGenerator` instances.

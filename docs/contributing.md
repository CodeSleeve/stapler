## Contributing
Stapler is always open to contributions, however there are a couple of guidelines you should follow:

1. Master will always contain the most recent, stable release.
2. Development will always have the newest work (bug fixes, new features, etc) and will always be ahead of master.  Every new tagged release will come from the work done on development (development will always be merged into master before a release is tagged).  If you want to contribute to Stapler, you must submit your pull request to development; it will always contain the most recent development work I'm doing.  **I WILL NOT BE MERGING PULL REQUESTS INTO MASTER**.

### Formating
I'm very particular about the way I format my php code.  In general, if you're submitting a pull request to Stapler, please adhere to the following guidelines and conventions:

If statements should always be wrapped in curly braces and contain a single space on both sides the parens.  If there is only a single line of code to be executed, please put the first curly brace on the same line as the condition:

```php
if (true) {
	$foo = $bar;
}
```

If there is more than one statement to be executed, each curly brace should appear on its own line:
```php
if (true) 
{
	$foo = $bar;
	$baz = $qux;
}
```

This formatting also applies to loops:
```php
foreach ($foo as $bar) {
	$baz = $qux;
}

foreach ($foo as $bar)
{
	$baz = $qux;
	$quux = $corge;
}
```

File and class names should always be camel cased.  Namespace and class declarations should always look like the following:
```php
<?php namespace Codesleeve\Stapler\Foo\Bar;


class Baz
{

}

?>
```

Functions should always include docblock headers with each curly brace on a new line:
```php
/**
 * A brief description of what the foo function does.
 *
 * @param string $name
 * @param array $baz
 */
function foo ($bar, array $baz) 
{
	//code
}
```

If a function has a return value, its type should also be listed in the docblock (the @return annotation should be omitted if there is no return value):
```php
/**
 * A brief description of what the foo function does.
 *
 * @param string $name
 * @param array $baz
 * @return array
 */
function foo ($bar, array $baz) 
{
	return $baz;
}
```

Variables should always be named using camelback syntax and should be expressive of the data they contain:

```php
$firstName = 'Travis';
$lastName = 'Bennett';
```
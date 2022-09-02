Coding Style Guide
==================

This coding style guide extends and expands on [PSR-12][].

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD", "SHOULD NOT", "RECOMMENDED", "MAY", and
"OPTIONAL" in this document are to be interpreted as described in [RFC 2119][].

[RFC 2119]: https://datatracker.ietf.org/doc/html/rfc2119

Rules
-----

* Public and protected properties MUST be declared before methods.
  Private properties SHOULD be declared before methods, but MAY be declared directly before the method that uses them.
  Properties MUST be declared in visibility order: public before protected before private.
  * Reason: More readable code.

* Public and protected static methods and properties MUST be accessed using `static`, except in the case of recursion:
  `self` MAY be used to to call the current implementation again instead of the child class's implementation.
  Constants MUST be accessed using `self`.
  Private static methods and properties MUST be accessed using `self`.
  A class name MUST NOT be used if it is the same as the current class, except in closures.
  * Reason: Using `static` (i.e. [late static binding](https://php.net/manual/en/language.oop5.late-static-bindings.php))
            allows a child class to override the method or the property.
            In the case of constants and private static methods and properties,
            using `static` would also allow overriding, so `self` must be used to prevent this.

* There MUST NOT be a space between the closing parenthesis and the colon in control structures using the alternative syntax.
  * Reason: This is how most people write code and how the examples in the
            [PHP manual](https://php.net/manual/en/control-structures.alternative-syntax.php) look like.
            [PSR-12][] ignores the alternative syntax, but the PSR-12 ruleset in PHPCS enforces one space.

* Alternative control structure syntax MUST NOT be used, except in view files.
  * Reason: There should not be two ways to do the same thing.
  * You may choose a ruleset that requires or forbids alternative syntax for view files.

* Arrays MUST use the short array syntax (`[]`).
  * Reason: Easier to read and write. There should not be two ways to do the same thing.

* Multi-line arrays MUST be declared with a single item on each line, indented once, and with a comma after every item.
  There MUST be at least one space before and exactly one space after the `=>` symbol.
  There MAY be more than one space after the `=>` symbol, but if so, all the `=>` symbols in the array declaration
  must be aligned to the same column.
  * Reason: Consistency with [PSR-12][] multi-line function call style.
            Comma after last line leads to cleaner diffs and makes it easier to add items.
            Aligned array values may be easier to read in some cases.

  ```php
  // bad
  $arr = [
      'foo' => 'bar',
      'hello'   => 'world',
      'longkey' => 'value',
  ];

  // bad
  $arr = [
      'baz',
      'foo'       => 'bar',
      'hello'     => 'world',
      'longkey'   => 'value',
  ];

  // good
  $arr = [
      'foo'   => 'bar',
      'baz',
      'hello' => 'world',
  ];

  // good
  $arr = [
      'baz',
      'foo' => 'bar',
      'hello' => 'world',
      'longkey' => 'value',
  ];

  // good
  $arr = [
      'baz',
      'foo'     => 'bar',
      'hello'   => 'world',
      'longkey' => 'value',
  ];
  ```

* Heredoc and nowdoc strings SHOULD NOT be used inside arrays.
  The comma after a heredoc or nowdoc item MUST be on a new line, and it MUST be indented once.

  ```php
  $arr = [
      'a' => <<<'EOT'
  a
  a
  EOT
      ,
      'b' => <<<'EOT'
  b
  b
  EOT
      ,
  ];
  ```

* Single-line array declarations MUST NOT have a space after the opening bracket and before the closing bracket.
  There MUST be a single space before and after the `=>` symbol.
  There MUST NOT be a space after the last item.

  ```php
  $arr = ['foo', 'bar' => 'baz'];
  ```

* There MUST NOT be a space after the opening square bracket and before the closing square bracket.
  There MUST NOT be a space before the opening square bracket.
  * Reason: Space before opening square bracket is confusing.

  ```php
  // bad
  $myArray  ['key'] = $value;
  $myArray[ 'key' ] = $value;

  // good
  $myArray['key'] = $value;
  ```

* There MUST NOT be a space before a semicolon.

* There MUST NOT be a space after a cast operator.
  ```php
  // bad
  $foo = (int) $bar;

  // good
  $foo = (int)$bar;
  ```

* All binary and ternary (but not unary) operators MUST be preceded and followed by one space.

* There MUST NOT be a space before or after an object operator.
  ```php
  // bad
  $foo -> bar();

  // good
  $foo->bar();
  ```

* Echoed strings MUST NOT be enclosed in parentheses.
  ```php
  // bad
  echo('foo');

  // good
  echo 'foo';
  ```

* Strings MUST use single quotes unless double quotes are required.
  ```php
  // bad
  $var = "foo";

  // good
  $var = 'foo';
  $var = "bar\n";
  $var = "bar$baz";
  ```

* The backtick operator MUST NOT be used.
  * Reason: `shell_exec` is identical and clearer. There should not be two ways to do the same thing.

* `is_null` MUST NOT be used. `=== null` or `!== null` SHOULD be used instead.
  * Reason: `is_null` and `=== null` have the same result. There should not be two ways to do the same thing.

* `sizeof`, `delete` and `print` MUST NOT be used. `count`, `unset`, `echo` SHOULD be used instead.
  * Reason: These are aliases. There should not be two ways to do the same thing.

* `create_function` MUST NOT be used.
  * Reason: Anonymous functions should be used instead.

* There MUST be a single space after `echo`, `print`, `return` and `new`.



References
==========

* [PSR-12][PSR-12]

[PSR-12]: https://www.php-fig.org/psr/psr-12/

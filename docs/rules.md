Coding Style Guide
==================

This coding style guide extends and expands on [PSR2].
It also incorporates several rules from the [Yii 2 Core Framework Code Style][Yii2].

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this document are to be
interpreted as described in [RFC 2119][].

[RFC 2119]: http://tools.ietf.org/html/rfc2119

Rules
-----

* Property and method names MUST start with an initial underscore if they are private.
  * Reason: Consistency with [Yii 2][Yii2]. Used by Yii's [property feature](http://www.yiiframework.com/doc-2.0/yii-base-object.html) to avoid name clashes.

* Public and protected properties MUST be declared before methods.
  Private properties SHOULD be declared before methods, but MAY be declared directly before the method that uses them.
  Properties MUST be declared in visibility order: public before protected before private.
  * Reason: Consistency with [Yii 2][Yii2]. More readable code.

* Public and protected static methods and properties MUST be accessed using `static`, except in the case of recursion:
  `self` MAY be used to to call the current implementation again instead of the child class's implementation.
  Constants MUST be accessed using `self`.
  Private static methods and properties MUST be accessed using `self`.
  A class name MUST NOT be used if it is the same as the current class, except in closures.
  * Reason: Using `static` (i.e. [late static binding](http://php.net/manual/en/language.oop5.late-static-bindings.php))
            allows a child class to override the method or the property.
            In the case of constants and private static methods and properties,
            using `static` would also allow overriding, so `self` must be used to prevent this.

* There MUST NOT be a space between the closing parenthesis and the colon in control structures using the alternative syntax.
  * Reason: This is how most people write code and how the examples in the
            [PHP manual](http://php.net/manual/en/control-structures.alternative-syntax.php) look like.
            [PSR2][PSR2] ignores the alternative syntax, but the PSR2 ruleset in PHPCS enforces one space.

* Alternative control structure syntax MUST NOT be used, except in view files.
  * Reason: There should not be two ways to do the same thing.
  * You may choose a ruleset that requires or forbids alternative syntax for view files.

* Control structures MAY be split into multiple lines.
  When doing so, there MUST be a newline after the opening parenthesis, each line MUST be indented once,
  and the closing parenthesis must be on the start of a new line, indented to the same depth as the control structure itself.
  * Reason: Putting the closing parenthesis on its own line with the opening brace makes it easier to see where the body begins.
            Putting the conditions into their own, indented block is similar to multi-line function calls in [PSR2][PSR2].
            PSR2 does not specify how multi-line control structures should look.

  ```php
  // bad
  if (
      ($condition1 || $condition2) &&
      $condition3) {
      // if body
  }

  // bad
  if (
  ($condition1 || $condition2) &&
  $condition3
  ) {
      // if body
  }
  
  // bad
  if (($condition1 || $condition2) &&
    $condition3) {
      // if body
  }
  
  // good
  if (
      ($condition1 || $condition2) &&
      $condition3
  ) {
      // if body
  }
  ```
  

* Arrays MUST use the short array syntax (`[]`).
  * Reason: Consistency with [Yii 2][Yii2]. Easier to read and write. There should not be two ways to do the same thing.

* Multi-line arrays MUST be declared with a single item on each line, indented once, and with a comma after every item.
  There MUST be at least one space before and exactly one space after the `=>` symbol.
  There MAY be more than one space after the `=>` symbol, but if so, all the `=>` symbols in the array declaration
  must be aligned to the same column.
  Keyless items SHOULD NOT be placed between items with keys, they SHOULD be placed before all items with keys.
  * Reason: Consistency with [PSR2][PSR2] multi-line function call style.
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
  Keyless items SHOULD be placed before all items with keys.

  ```php
  $arr = ['foo', 'bar' => 'baz'];
  ```

* There MUST NOT be a space after the opening square bracket and before the closing square bracket.
  There MUST NOT be a space before the opening square bracket.
  * Reason: Consistency with [Yii 2][Yii2] array declaration syntax. Space before opening square bracket is confusing.

  ```php
  // bad
  $myArray  ['key'] = $value;
  $myArray[ 'key' ] = $value;

  // good
  $myArray['key'] = $value;
  ```

* There MUST NOT be a space before a semicolon.

* There MUST NOT be any whitespace in cast operators.
* There MUST NOT be a space after a cast operator.
  ```php
  // bad
  $foo = ( int )$bar;
  $foo = (int) $bar;

  // good
  $foo = (int)$bar;
  $foo = (int)$bar;
  ```

* All binary and ternary (but not unary) operators MUST be preceded and followed by one space.

* There MUST NOT be a space before or after an object operator. A newline is allowed before the operator,
  but the next line SHOULD be indented.
  ```php
  // bad
  $foo -> bar();
  $foor->
    bar();

  // good
  $foo->bar();
  $foo->bar()
    ->baz()
    ->qux();
  ```

* Echoed strings MUST NOT be enclosed in parentheses.
  ```php
  // bad
  echo("foo");

  // good
  echo "foo";
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

* There MUST always be an opening and closing parenthesis in a `new` expression, even if there are no arguments.
  * Reason: Consistency with the case when there are arguments. There should not be two ways to do the same thing.
  ```php
  // bad
  $a = new Foo;

  // bad
  $c = new class {
      public function __construct()
      {
      }
  };
  
  // good
  $a = new Foo();

  // good
  $c = new class() {
      public function __construct()
      {
      }
  };
  ```

* `is_null` MUST NOT be used. `=== null` or `!== null` SHOULD be used instead.
  * Reason: `is_null` and `=== null` have the same result. There should not be two ways to do the same thing.

* `sizeof`, `delete` and `print` MUST NOT be used. `count`, `unset`, `echo` SHOULD be used instead.
  * Reason: These are aliases. There should not be two ways to do the same thing.

* `create_function` MUST NOT be used.
  * Reason: Anonymous functions should be used instead.

* There MUST be a single space after `echo`, `print`, `return` and `new`.



References
==========

* [Yii 2 Core Framework Code Style][Yii2]
* [PSR2][PSR2]

[Yii2]: https://github.com/yiisoft/yii2/blob/master/docs/internals/core-code-style.md
[PSR2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

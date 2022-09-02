Mito Yii 2 Coding Standards
===========================

This repository contains [Mito](https://mito.hu/)'s coding standards
for Yii 2 based applications.

![Code coverage](docs/clover.svg)

Getting the code
----------------

You can get code style definition using one of the following methods.

* Clone `hellowearemito/yii2-coding-standards` repository:

```
$ git clone git://github.com/hellowearemito/yii2-coding-standards.git
```

* Install using [composer](https://getcomposer.org/download/):

```
$ composer require mito/yii2-coding-standards:"~2.0.0@beta"
```

PHP_Codesniffer
---------------

This repository contains five phpcs standards:

* `Application`: for normal class files.
* `Views`: for view files, requires using curly braces for control structures.
* `ViewsAlternate`: for view files, requires using alternate syntax for control structures.
* `ViewsMixed`: for view files, allows both curly braces and alternate syntax.
* `Others`: for other files, such as configuration files and migration classes.

These standards are based on PSR-12, with some additions and improvements.

The `Views` standards disable some rules that cause problems when mixing php with html,
and therefore cannot fully check view files and cannot fix all problems in them,
so be careful when using phpcbf with view files.

For a full description of the rules, see [docs/rules.md](docs/rules.md).

### Using the standards

After CodeSniffer is installed, you can launch it with a custom standard using the following syntax:

```
$ ./vendor/bin/phpcs --extensions=php --standard=vendor/mito/yii2-coding-standards/Application .
```

To automatically fix most issues, use phpcbf:

```
$ ./vendor/bin/phpcbf --extensions=php --standard=vendor/mito/yii2-coding-standards/Application .
```

**Warning**: always check phpcbf's output and keep backups. If it breaks your code, you get to keep both pieces.

You should also run a syntax check using `php -l` before running phpcbf,
because some sniffs assume the code is syntactically valid, and may mangle it if it is not.

If you're using PhpStorm you can configure it to use CodeSniffer using Settings → PHP → Code Sniffer.
The standard can be specified at Inspections → PHP → PHP Code Sniffer validation.

Note that this will check all your files with a single standard, but you should use different standards
for different types of files.
These standards were designed for use with [gulp-phpcs](https://www.npmjs.com/package/gulp-phpcs) and [gulp-phpcbf](https://www.npmjs.com/package/gulp-phpcbf),
so instead of using exclusion patterns in the `ruleset.xml`, they rely on the gulp task selecting
the appropriate standard for a file.

You can write your own `ruleset.xml` that extends from the `Application` standard
and contains appropriate exclusion patterns (check the `Others` and `Views` `ruleset.xml` files for what rules to exclude).

### Useful links

* [Configuration options](http://pear.php.net/manual/en/package.php.php-codesniffer.config-options.php)
* [Manual and guide](http://pear.php.net/manual/en/package.php.php-codesniffer.php)
* [GitHub repository](https://github.com/squizlabs/PHP_CodeSniffer)

Contributing
------------

See [CONTRIBUTING.md](CONTRIBUTING.md) for information.

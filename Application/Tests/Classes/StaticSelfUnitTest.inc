<?php

class Test extends BaseTest
{
    const FOO = 'bar';

    public static $prop;

    public static function recursive($i)
    {
        if ($i < 0) {
            return;
        }
        // It is allowed to use self for recursion to call current implementation again instead of extending classes implementation.
        self::recursive($i--);
    }

    public static function recursive2($i)
    {
        if ($i < 0) {
            return;
        }
        // It is allowed to use self for recursion to call current implementation again instead of extending classes implementation.
        static::recursive2($i--);
    }

    private $_stuff = 'bar';

    public static function bar()
    {
        OtherClass::test();
        // always use static
        $a = static::$prop;
        static::foo();
        // inherited
        $b = static::$inheritedProp;
        static::inherited();
    }

    public static function barBad()
    {
        // always use static
        $a = self::$prop;
        self::foo();
        // inherited
        $b = self::$inheritedProp;
        self::inherited();
    }

    private static $_prop = [];

    private static function baz()
    {
        return 'foo';
    }

    public static function foo()
    {
        // private properties and methods must use self
        $a = self::$_prop;
        self::baz();
        // constants must use self
        return self::FOO . self::INHERITED_CONST;
    }

    public static function fooBad()
    {
        // private properties and methods must use self
        $a = static::$_prop;
        static::baz();
        // constants must use self
        return static::FOO . static::INHERITED_CONST;
    }

    public static function constVsMethod()
    {
        $this->on(self::EVENT, function () {
        });
        $this->on(static::EVENT, function () {
        });
    }

    public static function whoAmI()
    {
        $self = self::class;
        $static = static::class;
    }

    public static function recursiveNamed($i)
    {
        if ($i < 0) {
            return;
        }
        // Using the name is not allowed.
        Test::recursiveNamed($i--);
    }

    private static function recursivePrivate($i)
    {
        if ($i < 0) {
            return;
        }
        // Using the name is not allowed.
        Test::recursivePrivate($i--);
    }
}

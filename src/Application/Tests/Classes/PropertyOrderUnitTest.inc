<?php

class Good
{
    public $a;
    public $b;

    protected $c;
    protected $d;

    private $e;
    private $f;

    public function m1()
    {
    }

    public function m2($a, $b)
    {
        $aa = 42;
        $bb = function () {
            $aa = 47;
        };
        $bb();
    }

    private $m3p;

    public function m3()
    {
        if ($this->m3p !== true) {
            $this->m3p = true;
        }
    }
}

class Bad
{
    public $a;
    public $b;

    protected $c;
    protected $d;

    public $bad1;

    private $e;
    private $f;

    public $bad2;
    protected $bad3;
    protected $bad4;

    public function m1()
    {
    }

    public function m2($a, $b)
    {
        $aa = 42;
        $bb = function () {
            $aa = 47;
        };
        $bb();
    }

    protected $m3p;

    public function m3()
    {
        if ($this->m3p !== true) {
            $this->m3p = true;
        }
    }

    public $m4p;

    public function m4()
    {
        if ($this->m4p !== true) {
            $this->m4p = true;
        }
    }
}

interface SomeInterface
{
    public function foo();
    public function bar();
    public function baz();
}

abstract class AbstractGood
{
    public $property1;

    protected $property2;

    abstract protected function foo($a, $b);

    public function bar()
    {
        $this->foo($this->property1, $this->property2);
    }
}

abstract class AbstractBad
{
    public $property1;

    protected $property2;

    abstract protected function foo($a, $b);

    protected $property3;

    public function bar()
    {
        $this->foo($this->property1, $this->property2);
    }
}

<?php

echo is_null($a) ? 'null' : 'not null';
echo !is_null($a) ? 'not null' : 'null';

if (is_null($a)) {
    echo 'null';
} else {
    echo 'not null';
}
if (!is_null($a)) {
    echo 'not null';
} else {
    echo 'null';
}

if (!(is_null($a) || is_null($b))) {
    echo 'not null';
} else {
    echo 'null';
}

// namespaced, but still the same

echo \is_null($a) ? 'null' : 'not null';
echo !\is_null($a) ? 'not null' : 'null';

if (\is_null($a)) {
    echo 'null';
} else {
    echo 'not null';
}
if (!\is_null($a)) {
    echo 'not null';
} else {
    echo 'null';
}

if (!(\is_null($a) || \is_null($b))) {
    echo 'not null';
} else {
    echo 'null';
}

// different namespace

echo \utils\is_null($a) ? 'null' : 'not null';
echo !\utils\is_null($a) ? 'not null' : 'null';

if (\utils\is_null($a)) {
    echo 'null';
} else {
    echo 'not null';
}
if (!\utils\is_null($a)) {
    echo 'not null';
} else {
    echo 'null';
}

if (!(\utils\is_null($a) || \utils\is_null($b))) {
    echo 'not null';
} else {
    echo 'null';
}

// method

echo $class->is_null($a) ? 'null' : 'not null';
echo !$class->is_null($a) ? 'not null' : 'null';

if ($class->is_null($a)) {
    echo 'null';
} else {
    echo 'not null';
}
if (!$class->is_null($a)) {
    echo 'not null';
} else {
    echo 'null';
}

if (!($class->is_null($a) || $class->is_null($b))) {
    echo 'not null';
} else {
    echo 'null';
}

// static method

echo Utils::is_null($a) ? 'null' : 'not null';
echo !Utils::is_null($a) ? 'not null' : 'null';

if (Utils::is_null($a)) {
    echo 'null';
} else {
    echo 'not null';
}
if (!Utils::is_null($a)) {
    echo 'not null';
} else {
    echo 'null';
}

if (!(Utils::is_null($a) || Utils::is_null($b))) {
    echo 'not null';
} else {
    echo 'null';
}

trait SomeTrait {
    public function is_null() {
        //irrelevant
    }
}

class LeftSideTest {
    use SomeTrait {
        is_null as protected nullchecker;
    }
}

class RightSideTest {
    use DelProvider {
        is_null as is_null;
    }
}

class RightSideVisTest {
    use DelProvider {
        is_null as protected is_null;
        is_null insteadof is_null;
    }
}

namespace Something\is_null;
$var = new Is_Null();
class Is_Null implements Something {}

function mymodule_form_callback(Is_Null $is_null) {
}

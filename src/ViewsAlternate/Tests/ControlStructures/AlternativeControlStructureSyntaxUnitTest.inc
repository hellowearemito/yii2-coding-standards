<?php

if ($a === true) {
    echo 'a is true';
} elseif ($a === false) {
    echo 'a is false';
} else {
    echo 'a is something else';
}

$arr = ['a', 'b', 'c', 'd'];

foreach ($arr as $a) {
    echo $a;
}

for ($i = 0; $i < count($arr); $i++) {
    echo $arr[$i];
}

while (false) {
    echo 'never';
}

switch ($a) {
    case 1:
        echo 'one';
        break;
    case 2:
        echo 'two';
        break;
    case 3:
        echo 'three';
        break;
    default:
        echo 'something else';
        break;
}

?>

<?php if ($a === true) { ?>
    a is true
<?php } elseif ($a === false) { ?>
    a is false
<?php } else { ?>
    a is something else
<?php } /* end if */ ; ?>

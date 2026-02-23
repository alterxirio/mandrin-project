<?php

$map = [
    'drag-drop' =>'../question/drag-and-drop.php',
    'audio-image' => '../question/audio-image.php',
    'match-image' => '../question/match-image.php',
    'mcq-text' => '../question/mcq.php',
    'true-false' => '../question/true-false.php',

];

$type = $_GET['type'] ?? '';
if (isset($map[$type])) {
    include $map[$type];
}

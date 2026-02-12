<?php

$map = [
    'drag-drop' => __DIR__ . '/question-forms/drag-drop.php',
    'audio-image' => __DIR__ . '/question-forms/audio-image.php',
    'match-image' => __DIR__ . '/question-forms/match-image.php',
    'mcq-text' => __DIR__ . '/question-forms/mcq-text.php',
    'true-false' => __DIR__ . '/question-forms/true-false-image.php',
];

$type = $_GET['type'] ?? '';
if (isset($map[$type])) {
    include $map[$type];
}

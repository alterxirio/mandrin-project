<?php

    $map = [
        'drag-drop'   => '../question/drag-and-drop.php',
        'audio-image' => '../question/audio-image.php',
        'match-image' => '../question/match-image.php',
        'mcq-text'    => '../question/mcq.php',
        'true-false'  => '../question/true-false.php',
    ];

    if (isset($_GET['type']) && isset($map[$_GET['type']])) {
        include($map[$_GET['type']]);
    }

?>

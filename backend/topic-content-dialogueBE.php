<?php
include('../config/config.php');

$topik_id = $_GET['topik_id'];
$folder = "../media/audio/dialogue/dialogue-" . $topik_id . "/";

if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// if (isset($_POST['edit_chinese'])) {

//     $id = $_POST['edit_id'];
//     $chinese = $_POST['edit_chinese'];
//     $pinyin = $_POST['edit_pinyin'];
//     $meaning = $_POST['edit_meaning'];

//     // If new audio uploaded
//     if (isset($_FILES['edit_audio']) && $_FILES['edit_audio']['error'] == 0) {

//         // Get old audio path
//         $q = mysqli_query($con, "SELECT audio_path FROM dialogues WHERE id='$id'");
//         $d = mysqli_fetch_assoc($q);
//         $old_audio = $d['audio_path'];

//         if (file_exists($old_audio)) {
//             unlink($old_audio);
//         }

//         // Keep same filename number
//         $filename = basename($old_audio);
//         $destination = $folder . $filename;
//         move_uploaded_file($_FILES['edit_audio']['tmp_name'], $destination);

//         $sql = "UPDATE dialogues 
//                 SET chinese_text='$chinese',
//                     pinyin_text='$pinyin',
//                     meaning='$meaning',
//                     audio_path='$destination'
//                 WHERE id='$id'";

//     } else {

//         $sql = "UPDATE dialogues 
//                 SET chinese_text='$chinese',
//                     pinyin_text='$pinyin',
//                     meaning='$meaning'
//                 WHERE id='$id'";
//     }

//     mysqli_query($con, $sql);
//     header("Location: ../frontend/topic-content.php?id=$topik_id");
//     exit;
// }

if (isset($_POST['add_dialogue'])) {

    $add_dialogue = $_POST['add_dialogue'];
    $add_pinyinDialogue = $_POST['add_pinyinDialogue'];
    $add_character = $_POST['add_character'];
    $add_meaningDialogue = $_POST['add_meaningDialogue'];


    // Get next audio number
    $countQuery = mysqli_query($con, "SELECT COUNT(*) AS total FROM dialogues WHERE topic_id='$topik_id'");
    $countData = mysqli_fetch_assoc($countQuery);
    $nextNumber = $countData['total'] + 1;

    $audioName = $nextNumber . ".mp3";
    $destination = $folder . $audioName;

    if (isset($_FILES['add_audioDialogue']) && $_FILES['add_audioDialogue']['error'] == 0) {
        move_uploaded_file($_FILES['add_audioDialogue']['tmp_name'], $destination);
    } else {
        $destination = "";
    }

    mysqli_query(
        $con,
        "INSERT INTO dialogues (topic_id, chinese_text, pinyin_text, meaning, character_name, audio_path)
         VALUES ('$topik_id','$add_dialogue','$add_pinyinDialogue','$add_meaningDialogue','$add_character','$destination')"
    );

    // header("Location: ../frontend/topic-content.php?id=$topik_id");
    exit;
}

// if (isset($_GET['delete-id'])) {

//     $id = $_GET['delete-id'];

//     $query = mysqli_query($con, "SELECT audio_path FROM dialogues WHERE id='$id'");
//     $data = mysqli_fetch_assoc($query);

//     if ($data && file_exists($data['audio_path'])) {
//         unlink($data['audio_path']);
//     }

//     mysqli_query($con, "DELETE FROM dialogues WHERE id='$id'");

//     header("Location: ../frontend/topic-content.php?id=$topik_id");
//     exit;
// }
?>

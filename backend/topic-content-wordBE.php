<?php
include('../config/config.php');

// Folder to save
$folder = "../media/audio/topik-".$_GET["topik_id"]."/";

// Detect UPDATE
if (isset($_POST['edit_name'])) {
    
    $id = $_POST['edit_id'];
    $topik_id = $_GET["topik_id"];
    $meaning = $_POST['edit_meaning'];
    $name = $_POST['edit_name'];
    $pinyin = $_POST['edit_pinyin'];

    echo $topik_id;
    echo $meaning;
    echo $name;
    echo $pinyin;
    


    // Check if a new banner is uploaded
    if(isset($_FILES['edit_audio']) && $_FILES['edit_audio']['error'] === 0) {
        $newName = "topik " . $_GET["topik_id"]." - ".$_POST['edit_name'].".mp3";
        $destination = $folder . $newName;
        move_uploaded_file($_FILES['edit_audio']['tmp_name'], $destination);

        // Update with banner
        $sql = "UPDATE words 
                SET topic_id='$topik_id', chinese='$name', pinyin='$pinyin', meaning='$meaning', audio_path='$destination'
                WHERE id='$id'";


    } else {
        // Update without banner
        $sql = "UPDATE words 
                SET topic_id='$topik_id', chinese='$name', pinyin='$pinyin', meaning='$meaning'
                WHERE id='$id'";


    }

    "3";
    mysqli_query($con, $sql);
    header("Location: ../frontend/topic-content.php?id=$topik_id");
    exit;
}


if (isset($_POST['add_name'])) {

    // Otherwise → ADD NEW TOPIC
    // $sql = "SELECT topik FROM topics ORDER BY topik DESC LIMIT 1";
    // $result = mysqli_query($con, $sql);
    // $row = mysqli_fetch_assoc($result);
    // $lastTopic = $row ? $row['topik'] : 0;

    // $newTopicNumber = $lastTopic + 1;

    // Check banner upload
    if(isset($_FILES['add_audio']) && $_FILES['add_audio']['error'] === 0) {
        $newName = "topik " . $_GET["topik_id"]." - ".$_POST['add_name'].".mp3";
        $destination = $folder . $newName;
        move_uploaded_file($_FILES['add_audio']['tmp_name'], $destination);
    } else {
        $destination = $folder . "defaut-audio.mp3";
    }

    $topik_id = $_GET["topik_id"];
    $meaning = $_POST['add_meaning'];
    $name = $_POST['add_name'];
    $pinyin = $_POST['add_pinyin'];

    mysqli_query($con, "INSERT INTO words VALUES ('','$topik_id','$name','$pinyin','$meaning','$destination')");

    header("Location:  ../frontend/topic-content.php?id=$topik_id");
}

if (isset($_GET['delete-id'])) {

    $id = $_GET['delete-id'];
    
    $query = mysqli_query($con, "SELECT audio_path FROM words WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query);
    $file_path = $data['audio_path'];

    if(file_exists($file_path)) {
        unlink($file_path);
    }

    mysqli_query($con, "DELETE FROM words WHERE id = '$id'");
    $topik_id = $_GET["topic-id"];

    header("Location:  ../frontend/topic-content.php?id=$topik_id");

}

?>

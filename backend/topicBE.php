<?php
include('../config/config.php');

// Folder to save
$folder = "../media/graphic/";

// Detect UPDATE
if (isset($_POST['edit_name'])) {

    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $character = $_POST['edit_character'];
    $pinyin = $_POST['edit_pinyin'];

    // Check if a new banner is uploaded
    if(isset($_FILES['edit_banner']) && $_FILES['edit_banner']['error'] === 0) {
        $newName = "Banner - " . $id . ".png";
        $destination = $folder . $newName;
        move_uploaded_file($_FILES['edit_banner']['tmp_name'], $destination);

        // Update with banner
        $sql = "UPDATE topics 
                SET topic_name='$name', chinese_character='$character', pinyin='$pinyin', banner_path='$destination'
                WHERE topik='$id'";

                echo $id;
                echo"1";


    } else {
        // Update without banner
        $sql = "UPDATE topics 
                SET topic_name='$name', chinese_character='$character', pinyin='$pinyin'
                WHERE topik='$id'";

                echo"2";

    }

    "3";
    mysqli_query($con, $sql);
    header("Location: ../frontend/main.php");
    exit;
}


if (isset($_POST['add_name'])) {

    // Otherwise → ADD NEW TOPIC
    $sql = "SELECT topik FROM topics ORDER BY topik DESC LIMIT 1";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    $lastTopic = $row ? $row['topik'] : 0;

    $newTopicNumber = $lastTopic + 1;

    // Check banner upload
    if(isset($_FILES['add_banner']) && $_FILES['add_banner']['error'] === 0) {
        $newName = "Banner - " . $newTopicNumber . ".png";
        $destination = $folder . $newName;
        move_uploaded_file($_FILES['add_banner']['tmp_name'], $destination);
    } else {
        $destination = $folder . "Banner - 1.png";
    }

    $name = $_POST['add_name'];
    $character = $_POST['add_character'];
    $pinyin = $_POST['add_pinyin'];

    mysqli_query($con, "INSERT INTO topics VALUES ('','$newTopicNumber','$name','$character','$pinyin','$destination')");

    header("Location: ../frontend/main.php");
}

if (isset($_GET['delete-id'])) {

    $id = $_GET['delete-id'];

     // 1. Get the file path from DB
    $query = mysqli_query($con, "SELECT banner_path FROM topics WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query);
    $file_path = $data['banner_path'];

    // 2. Delete the file
    if(file_exists($file_path)) {
        unlink($file_path);
    }

    // 3. Delete the topic from DB
    mysqli_query($con, "DELETE FROM topics WHERE id = '$id'");

    header("Location: ../frontend/main.php");

}

?>

<?php
include('../config/config.php');
require_once __DIR__ . '/../config/upload.php';


// Relative folder path from project root
$folder = '../media/graphic';


// ==========================================
// 1. UPDATE TOPIC
// ==========================================
if (isset($_POST['edit_name'])) {

    $id        = $_POST['edit_id'];
    $name      = $_POST['edit_name'];
    $character = $_POST['edit_character'];
    $pinyin    = $_POST['edit_pinyin'];


    // Check if new banner image was uploaded
    if (isset($_FILES['edit_banner']) && $_FILES['edit_banner']['error'] === UPLOAD_ERR_OK) {

        // ==========================================
        // GET OLD BANNER PATH
        // ==========================================
        $oldBanner = null;

        $stmtOld = mysqli_prepare(
            $con,
            "SELECT banner_path FROM topics WHERE topik = ?"
        );

        mysqli_stmt_bind_param($stmtOld, "i", $id);
        mysqli_stmt_execute($stmtOld);

        $resultOld = mysqli_stmt_get_result($stmtOld);

        if ($dataOld = mysqli_fetch_assoc($resultOld)) {
            $oldBanner = $dataOld['banner_path'];
        }

        mysqli_stmt_close($stmtOld);


        // ==========================================
        // DELETE OLD BANNER
        // ==========================================
        if ($oldBanner && !str_contains($oldBanner, 'Banner - 1.png')) {
            removeUploadedFile($oldBanner);
        }


        // ==========================================
        // SAVE NEW BANNER
        // ==========================================
        $fileName = "Banner - " . $id . ".png";

        $destination = saveUploadedFile(
            'edit_banner',
            $folder,
            $fileName
        );


        // ==========================================
        // UPDATE DATABASE
        // ==========================================
        if ($destination !== null) {

            $stmt = mysqli_prepare(
                $con,
                "UPDATE topics 
                 SET topic_name=?, chinese_character=?, pinyin=?, banner_path=? 
                 WHERE topik=?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssi",
                $name,
                $character,
                $pinyin,
                $destination,
                $id
            );

        } else {

            $stmt = mysqli_prepare(
                $con,
                "UPDATE topics 
                 SET topic_name=?, chinese_character=?, pinyin=? 
                 WHERE topik=?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $name,
                $character,
                $pinyin,
                $id
            );
        }

    } else {

        // ==========================================
        // UPDATE WITHOUT CHANGING BANNER
        // ==========================================

        $stmt = mysqli_prepare(
            $con,
            "UPDATE topics 
             SET topic_name=?, chinese_character=?, pinyin=? 
             WHERE topik=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $name,
            $character,
            $pinyin,
            $id
        );
    }


    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);


    header("Location: ../frontend/main.php");
    exit;
}


// ==========================================
// 2. ADD NEW TOPIC
// ==========================================
if (isset($_POST['add_name'])) {

    // Calculate next topic number
    $sql = "SELECT topik FROM topics ORDER BY topik DESC LIMIT 1";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);

    $newTopicNumber = ($row ? (int)$row['topik'] : 0) + 1;


    $fileName = "Banner - " . $newTopicNumber . ".png";


    // ==========================================
    // HANDLE UPLOADED FILE
    // ==========================================
    if (
        isset($_FILES['add_banner']) &&
        $_FILES['add_banner']['error'] === UPLOAD_ERR_OK
    ) {

        $savedPath = saveUploadedFile(
            'add_banner',
            $folder,
            $fileName
        );

        $destination = $savedPath ?? ($folder . '/Banner - 1.png');

    } else {

        $destination = $folder . '/Banner - 1.png';
    }


    $name      = $_POST['add_name'];
    $character = $_POST['add_character'];
    $pinyin    = $_POST['add_pinyin'];


    // ==========================================
    // INSERT NEW TOPIC
    // ==========================================
    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO topics 
        (topik, topic_name, chinese_character, pinyin, banner_path) 
        VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "issss",
        $newTopicNumber,
        $name,
        $character,
        $pinyin,
        $destination
    );


    if (mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        header("Location: ../frontend/main.php");
        exit;

    } else {

        echo "Error: " . mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);
    }
}


// ==========================================
// 3. DELETE TOPIC
// ==========================================
if (isset($_GET['delete-id'])) {

    $id = $_GET['delete-id'];


    // ==========================================
    // GET FILE PATH FROM DATABASE
    // ==========================================
    $stmt = mysqli_prepare(
        $con,
        "SELECT banner_path FROM topics WHERE topik = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    if ($data = mysqli_fetch_assoc($result)) {

        // Protect default fallback image
        if (
            $data['banner_path'] &&
            !str_contains($data['banner_path'], 'Banner - 1.png')
        ) {

            removeUploadedFile($data['banner_path']);
        }
    }

    mysqli_stmt_close($stmt);


    // ==========================================
    // DELETE TOPIC FROM DATABASE
    // ==========================================
    $stmtDelete = mysqli_prepare(
        $con,
        "DELETE FROM topics WHERE topik = ?"
    );

    mysqli_stmt_bind_param(
        $stmtDelete,
        "i",
        $id
    );

    mysqli_stmt_execute($stmtDelete);
    mysqli_stmt_close($stmtDelete);


    header("Location: ../frontend/main.php");
    exit;
}

?>
<?php
include('../config/config.php');
require_once __DIR__ . '/../config/upload.php';

$folder = '../media/graphic';
$defaultBanner = '../media/graphic/Banner - 1.png';

function redirectToTopics(): void
{
    header("Location: ../frontend/main.php");
    exit;
}

function fetchTopicBanner(mysqli $con, int $topicNumber): ?string
{
    $stmt = mysqli_prepare($con, "SELECT banner_path FROM topics WHERE topik = ?");
    mysqli_stmt_bind_param($stmt, "i", $topicNumber);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row['banner_path'] ?? null;
}

function saveTopicBanner(string $formKey, int $topicNumber, string $folder): ?string
{
    if (!isset($_FILES[$formKey]) || $_FILES[$formKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $fileName = "Banner - " . $topicNumber . ".png";
    return saveUploadedFile($formKey, $folder, $fileName, ['image/png']);
}

if (isset($_POST['edit_name'])) {
    $id = (int)$_POST['edit_id'];
    $name = trim($_POST['edit_name']);
    $character = trim($_POST['edit_character']);
    $pinyin = trim($_POST['edit_pinyin']);

    $destination = saveTopicBanner('edit_banner', $id, $folder);

    if ($destination !== null) {
        $oldBanner = fetchTopicBanner($con, $id);
        if ($oldBanner && $oldBanner !== $destination && $oldBanner !== $GLOBALS['defaultBanner']) {
            removeUploadedFile($oldBanner);
        }

        $stmt = mysqli_prepare(
            $con,
            "UPDATE topics SET topic_name = ?, chinese_character = ?, pinyin = ?, banner_path = ? WHERE topik = ?"
        );
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $character, $pinyin, $destination, $id);
    } else {
        $stmt = mysqli_prepare(
            $con,
            "UPDATE topics SET topic_name = ?, chinese_character = ?, pinyin = ? WHERE topik = ?"
        );
        mysqli_stmt_bind_param($stmt, "sssi", $name, $character, $pinyin, $id);
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    redirectToTopics();
}

if (isset($_POST['add_name'])) {
    $result = mysqli_query($con, "SELECT topik FROM topics ORDER BY topik DESC LIMIT 1");
    $row = mysqli_fetch_assoc($result);
    $newTopicNumber = ($row ? (int)$row['topik'] : 0) + 1;

    $destination = saveTopicBanner('add_banner', $newTopicNumber, $folder) ?? $defaultBanner;

    $name = trim($_POST['add_name']);
    $character = trim($_POST['add_character']);
    $pinyin = trim($_POST['add_pinyin']);

    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO topics (topik, topic_name, chinese_character, pinyin, banner_path) VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "issss", $newTopicNumber, $name, $character, $pinyin, $destination);

    if (!mysqli_stmt_execute($stmt)) {
        echo "Error: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);
    redirectToTopics();
}

if (isset($_GET['delete-id'])) {
    $id = (int)$_GET['delete-id'];
    $banner = fetchTopicBanner($con, $id);

    if ($banner && $banner !== $defaultBanner) {
        removeUploadedFile($banner);
    }

    $stmtDelete = mysqli_prepare($con, "DELETE FROM topics WHERE topik = ?");
    mysqli_stmt_bind_param($stmtDelete, "i", $id);
    mysqli_stmt_execute($stmtDelete);
    mysqli_stmt_close($stmtDelete);

    redirectToTopics();
}
?>

<?php
include('../config/config.php');
require_once __DIR__ . '/../config/upload.php';

function topicAudioFolder(string $topicId): string {
    return "media/audio/topik-{$topicId}";
}

function sanitizeAudioName(string $value): string {
    $sanitized = preg_replace('/[\\\\\/:*?"<>|]+/u', '', trim($value));
    $sanitized = preg_replace('/\s+/u', ' ', $sanitized);

    return $sanitized !== '' ? $sanitized : 'audio';
}

function saveWordAudio(string $fileKey, string $topicId, string $wordName): ?string {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) {
        return null;
    }

    $folder = topicAudioFolder($topicId);
    $extension = strtolower((string)pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    if ($extension === '') {
        $extension = 'mp3';
    }

    $safeWordName = sanitizeAudioName($wordName);
    $newName = "topik {$topicId} - {$safeWordName}.{$extension}";
    return saveUploadedFile($fileKey, $folder, $newName);
}

if (isset($_POST['edit_name'])) {
    $id = $_POST['edit_id'];
    $topik_id = $_GET['topik_id'];
    $meaning = $_POST['edit_meaning'];
    $name = $_POST['edit_name'];
    $pinyin = $_POST['edit_pinyin'];

    if (isset($_FILES['edit_audio']) && $_FILES['edit_audio']['error'] === 0) {
        $destination = saveWordAudio('edit_audio', (string)$topik_id, $name);

        if ($destination !== null) {
            $sql = "UPDATE words
                    SET topic_id='$topik_id', chinese='$name', pinyin='$pinyin', meaning='$meaning', audio_path='$destination'
                    WHERE id='$id'";
        } else {
            $sql = "UPDATE words
                    SET topic_id='$topik_id', chinese='$name', pinyin='$pinyin', meaning='$meaning'
                    WHERE id='$id'";
        }
    } else {
        $sql = "UPDATE words
                SET topic_id='$topik_id', chinese='$name', pinyin='$pinyin', meaning='$meaning'
                WHERE id='$id'";
    }

    mysqli_query($con, $sql);
    header("Location: ../frontend/topic-content.php?id=$topik_id");
    exit;
}

if (isset($_POST['add_name'])) {
    if (isset($_FILES['add_audio']) && $_FILES['add_audio']['error'] === 0) {
        $destination = saveWordAudio('add_audio', (string)$_GET['topik_id'], (string)$_POST['add_name']);
        if ($destination === null) {
            $destination = '../media/audio/default-audio.mp3';
        }
    } else {
        $destination = '../media/audio/default-audio.mp3';
    }

    $topik_id = $_GET['topik_id'];
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

    removeUploadedFile($file_path);

    mysqli_query($con, "DELETE FROM words WHERE id = '$id'");
    $topik_id = $_GET['topic-id'];

    header("Location:  ../frontend/topic-content.php?id=$topik_id");
}

?>

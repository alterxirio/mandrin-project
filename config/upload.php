<?php
function projectPath(string $relativePath): string
{
    return dirname(__DIR__) . '/' . ltrim($relativePath, '/');
}

function webPath(string $relativePath): string
{
    return ltrim(str_replace('\\', '/', $relativePath), '/');
}

function ensureWritableDirectory(string $relativeDirectory): ?string
{
    $absoluteDirectory = projectPath($relativeDirectory);

    if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0755, true)) {
        return null;
    }

    if (is_dir($absoluteDirectory) && !is_writable($absoluteDirectory)) {
        @chmod($absoluteDirectory, 0755);
    }

    return is_writable($absoluteDirectory) ? $absoluteDirectory : null;
}

function saveUploadedFile(string $formKey, string $relativeDirectory, string $fileName): ?string
{
    if (!isset($_FILES[$formKey]) || $_FILES[$formKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $absoluteDirectory = ensureWritableDirectory($relativeDirectory);
    if ($absoluteDirectory === null) {
        return null;
    }

    $safeFileName = basename($fileName);
    $relativePath = webPath($relativeDirectory . '/' . $safeFileName);
    $absolutePath = $absoluteDirectory . '/' . $safeFileName;

    if (!move_uploaded_file($_FILES[$formKey]['tmp_name'], $absolutePath)) {
        return null;
    }

    @chmod($absolutePath, 0644);

    return $relativePath;
}

function removeUploadedFile(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }

    $absolutePath = projectPath($relativePath);
    if (is_file($absolutePath)) {
        unlink($absolutePath);
    }
}
?>

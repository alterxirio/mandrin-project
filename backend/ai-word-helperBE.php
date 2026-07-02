<?php

header('Content-Type: application/json; charset=utf-8');
include('../config/config.php');

const OPENAI_API_KEY = $apiAppKey;
const OPENAI_MODEL = 'gpt-4.1-mini';

function sendJson(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cleanInput(string $value, int $maxLength = 300): string {
    $value = trim(strip_tags($value));

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return substr($value, 0, $maxLength);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$chinese = cleanInput((string)($_POST['chinese'] ?? ''), 80);
$pinyin = cleanInput((string)($_POST['pinyin'] ?? ''), 160);
$meaning = cleanInput((string)($_POST['meaning'] ?? ''), 200);

if ($chinese === '') {
    sendJson(['success' => false, 'message' => 'Missing Mandarin word.'], 400);
}

if (OPENAI_API_KEY === 'PASTE_YOUR_OPENAI_API_KEY_HERE' || OPENAI_API_KEY === '') {
    sendJson([
        'success' => false,
        'message' => 'AI API key is not configured yet. Please add your OpenAI API key in backend/ai-word-helperBE.php.'
    ], 500);
}

$prompt = <<<PROMPT
You are a Mandarin learning assistant for beginner students in Malaysia.
Explain this Mandarin word simply.

Word: {$chinese}
Pinyin: {$pinyin}
Meaning: {$meaning}

Return only valid JSON with these exact keys:
{
  "pronunciation": "Short Malay/English pronunciation guide. Mention tones if useful.",
  "translation": "Simple Malay translation and short explanation.",
  "sentence_chinese": "One short beginner Mandarin sentence using the word.",
  "sentence_pinyin": "Pinyin for the example sentence.",
  "sentence_translation": "Malay translation for the example sentence."
}
PROMPT;

$requestPayload = [
    'model' => OPENAI_MODEL,
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You return only valid JSON and no extra text.'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'response_format' => [
        'type' => 'json_object'
    ]
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . OPENAI_API_KEY,
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($requestPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    sendJson(['success' => false, 'message' => 'AI request failed: ' . $curlError], 500);
}

$decoded = json_decode($response, true);
if (!is_array($decoded)) {
    sendJson(['success' => false, 'message' => 'AI returned an invalid response.'], 500);
}

if ($statusCode < 200 || $statusCode >= 300) {
    $message = $decoded['error']['message'] ?? 'AI service returned an error.';
    sendJson(['success' => false, 'message' => $message], 500);
}

$outputText = trim((string)($decoded['choices'][0]['message']['content'] ?? ''));
$aiResult = json_decode($outputText, true);

if (!is_array($aiResult)) {
    sendJson(['success' => false, 'message' => 'AI response could not be parsed.'], 500);
}

sendJson([
    'success' => true,
    'pronunciation' => (string)($aiResult['pronunciation'] ?? ''),
    'translation' => (string)($aiResult['translation'] ?? ''),
    'sentence_chinese' => (string)($aiResult['sentence_chinese'] ?? ''),
    'sentence_pinyin' => (string)($aiResult['sentence_pinyin'] ?? ''),
    'sentence_translation' => (string)($aiResult['sentence_translation'] ?? '')
]);

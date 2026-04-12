<?php
include('../config/config.php');

$topik_id = isset($_GET['topik_id']) ? (int)$_GET['topik_id'] : 0;
$folder = "../media/audio/dialogue/dialogue-" . $topik_id . "/";

if ($topik_id > 0 && !is_dir($folder)) {
    mkdir($folder, 0777, true);
}

function ensureDefaultScenario(mysqli $con, int $topicId): int {
    if (!hasScenarioSchema($con)) {
        return 0;
    }

    if ($topicId <= 0) {
        return 0;
    }

    $stmt = $con->prepare("SELECT id FROM dialogue_scenarios WHERE topic_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
    $stmt->bind_param("i", $topicId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    if (isset($stmt)) {
        $stmt->close();
    }

    if ($row && isset($row['id'])) {
        return (int)$row['id'];
    }

    $defaultName = 'Scenario 1';
    $sortOrder = 1;

    $insert = $con->prepare("INSERT INTO dialogue_scenarios (topic_id, scenario_name, sort_order) VALUES (?, ?, ?)");
    $insert->bind_param("isi", $topicId, $defaultName, $sortOrder);
    $insert->execute();
    $newId = (int)$insert->insert_id;
    $insert->close();

    return $newId;
}

function nextLineNo(mysqli $con, int $scenarioId): int {
    if (!hasScenarioSchema($con)) {
        return 1;
    }

    if ($scenarioId <= 0) {
        return 1;
    }

    $stmt = $con->prepare("SELECT COALESCE(MAX(line_no), 0) + 1 AS next_line FROM dialogues WHERE scenario_id = ?");
    $stmt->bind_param("i", $scenarioId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    if (isset($stmt)) {
        $stmt->close();
    }

    return (int)($row['next_line'] ?? 1);
}

function hasScenarioSchema(mysqli $con): bool {
    static $checked = false;
    static $supported = false;

    if ($checked) {
        return $supported;
    }

    $checked = true;

    $tableExists = false;
    $columnScenarioExists = false;
    $columnLineExists = false;

    $result = $con->query("SHOW TABLES LIKE 'dialogue_scenarios'");
    if ($result instanceof mysqli_result) {
        $tableExists = $result->num_rows > 0;
        $result->free();
    }

    $result = $con->query("SHOW COLUMNS FROM dialogues LIKE 'scenario_id'");
    if ($result instanceof mysqli_result) {
        $columnScenarioExists = $result->num_rows > 0;
        $result->free();
    }

    $result = $con->query("SHOW COLUMNS FROM dialogues LIKE 'line_no'");
    if ($result instanceof mysqli_result) {
        $columnLineExists = $result->num_rows > 0;
        $result->free();
    }

    $supported = $tableExists && $columnScenarioExists && $columnLineExists;
    return $supported;
}

function isAjaxRequest(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function respondScenarioResult(bool $success, string $message, int $topicId): void {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        http_response_code($success ? 200 : 422);
        echo json_encode([
            'success' => $success,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    header("Location: ../frontend/topic-content.php?id=$topicId&section=dialogue");
    exit;
}

if (isset($_POST['ajax_scenarios'])) {
    header('Content-Type: application/json');

    if (!hasScenarioSchema($con)) {
        echo json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $stmt = $con->prepare("SELECT id, topic_id, scenario_name, sort_order, created_at FROM dialogue_scenarios WHERE topic_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->bind_param("i", $topik_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
    }

    $stmt->close();
    echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (isset($_POST['ajax_dialogues_by_scenario'])) {
    header('Content-Type: application/json');

    $scenarioId = isset($_POST['scenario_id']) ? (int)$_POST['scenario_id'] : 0;

    if (hasScenarioSchema($con)) {
        $stmt = $con->prepare("SELECT * FROM dialogues WHERE scenario_id = ? ORDER BY line_no ASC, id ASC");
        $stmt->bind_param("i", $scenarioId);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = null;
    }

    $items = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
    }

    $stmt->close();
    echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (isset($_POST['add_scenario_name'])) {
    $scenarioName = trim($_POST['add_scenario_name']);
    $sortOrder = isset($_POST['add_scenario_sort']) ? (int)$_POST['add_scenario_sort'] : 1;

    if ($scenarioName === '') {
        respondScenarioResult(false, 'Nama situasi tidak boleh kosong.', $topik_id);
    }

    if ($topik_id <= 0 || !hasScenarioSchema($con)) {
        respondScenarioResult(false, 'Skema situasi/dialog tidak tersedia untuk topik ini.', $topik_id);
    }

    if ($scenarioName !== '' && $topik_id > 0 && hasScenarioSchema($con)) {
        $stmt = $con->prepare("INSERT INTO dialogue_scenarios (topic_id, scenario_name, sort_order) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $topik_id, $scenarioName, $sortOrder);
        $ok = $stmt->execute();
        $errorNo = $con->errno;
        $stmt->close();

        if (!$ok) {
            if ($errorNo === 1062) {
                respondScenarioResult(false, 'Nama situasi sudah wujud untuk topik ini.', $topik_id);
            }
            respondScenarioResult(false, 'Gagal simpan situasi. Sila cuba lagi.', $topik_id);
        }
    }

    respondScenarioResult(true, 'Situasi berjaya disimpan.', $topik_id);
}

if (isset($_POST['edit_scenario_id']) && isset($_POST['edit_scenario_name'])) {
    $scenarioId = (int)$_POST['edit_scenario_id'];
    $scenarioName = trim($_POST['edit_scenario_name']);
    $sortOrder = isset($_POST['edit_scenario_sort']) ? (int)$_POST['edit_scenario_sort'] : 1;

    if ($scenarioName === '' || $scenarioId <= 0) {
        respondScenarioResult(false, 'Maklumat situasi tidak sah.', $topik_id);
    }

    if ($topik_id <= 0 || !hasScenarioSchema($con)) {
        respondScenarioResult(false, 'Skema situasi/dialog tidak tersedia untuk topik ini.', $topik_id);
    }

    if ($scenarioId > 0 && $scenarioName !== '' && hasScenarioSchema($con)) {
        $stmt = $con->prepare("UPDATE dialogue_scenarios SET scenario_name = ?, sort_order = ? WHERE id = ? AND topic_id = ?");
        $stmt->bind_param("siii", $scenarioName, $sortOrder, $scenarioId, $topik_id);
        $ok = $stmt->execute();
        $errorNo = $con->errno;
        $stmt->close();

        if (!$ok) {
            if ($errorNo === 1062) {
                respondScenarioResult(false, 'Nama situasi sudah wujud untuk topik ini.', $topik_id);
            }
            respondScenarioResult(false, 'Gagal kemas kini situasi. Sila cuba lagi.', $topik_id);
        }
    }

    respondScenarioResult(true, 'Situasi berjaya dikemas kini.', $topik_id);
}

if (isset($_GET['delete-scenario-id'])) {
    $scenarioId = (int)$_GET['delete-scenario-id'];

    if ($scenarioId > 0 && $topik_id > 0 && hasScenarioSchema($con)) {
        $stmt = $con->prepare("DELETE FROM dialogue_scenarios WHERE id = ? AND topic_id = ?");
        $stmt->bind_param("ii", $scenarioId, $topik_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../frontend/topic-content.php?id=$topik_id&section=dialogue");
    exit;
}

if (isset($_POST['edit_dialogue_id'])) {
    $edit_dialogue_id = (int)$_POST['edit_dialogue_id'];
    $edit_dialogue = $_POST['edit_dialogue'];
    $edit_pinyinDialogue = $_POST['edit_pinyinDialogue'];
    $edit_character = $_POST['edit_character'];
    $edit_meaningDialogue = $_POST['edit_meaningDialogue'];

    $useScenarioSchema = hasScenarioSchema($con);
    $scenarioId = isset($_POST['edit_scenario_id']) ? (int)$_POST['edit_scenario_id'] : 0;
    if ($useScenarioSchema && $scenarioId <= 0) {
        $scenarioId = ensureDefaultScenario($con, $topik_id);
    }

    $lineNo = isset($_POST['edit_line_no']) ? (int)$_POST['edit_line_no'] : 0;
    if ($useScenarioSchema && $lineNo <= 0) {
        $lineNo = nextLineNo($con, $scenarioId);
    }

    if (isset($_FILES['edit_audioDialogue']) && $_FILES['edit_audioDialogue']['error'] == 0) {
        $q = $con->prepare("SELECT audio_path FROM dialogues WHERE id = ?");
        $q->bind_param("i", $edit_dialogue_id);
        $q->execute();
        $result = $q->get_result();
        $d = $result ? $result->fetch_assoc() : null;
        $q->close();

        $old_audio = $d['audio_path'] ?? '';
        if ($old_audio !== '' && file_exists($old_audio)) {
            unlink($old_audio);
        }

        if ($useScenarioSchema) {
            $filename = $scenarioId . '-' . $lineNo . '-' . uniqid('', true) . '.mp3';
        } else {
            $filename = time() . '-' . uniqid('', true) . '.mp3';
        }
        $destination = $folder . $filename;
        move_uploaded_file($_FILES['edit_audioDialogue']['tmp_name'], $destination);

        if ($useScenarioSchema) {
            $sql = $con->prepare("UPDATE dialogues SET scenario_id = ?, line_no = ?, chinese_text = ?, pinyin_text = ?, meaning = ?, character_name = ?, audio_path = ? WHERE id = ?");
            $sql->bind_param("iisssssi", $scenarioId, $lineNo, $edit_dialogue, $edit_pinyinDialogue, $edit_meaningDialogue, $edit_character, $destination, $edit_dialogue_id);
        } else {
            $sql = $con->prepare("UPDATE dialogues SET chinese_text = ?, pinyin_text = ?, meaning = ?, character_name = ?, audio_path = ? WHERE id = ?");
            $sql->bind_param("sssssi", $edit_dialogue, $edit_pinyinDialogue, $edit_meaningDialogue, $edit_character, $destination, $edit_dialogue_id);
        }
    } else {
        if ($useScenarioSchema) {
            $sql = $con->prepare("UPDATE dialogues SET scenario_id = ?, line_no = ?, chinese_text = ?, pinyin_text = ?, meaning = ?, character_name = ? WHERE id = ?");
            $sql->bind_param("iissssi", $scenarioId, $lineNo, $edit_dialogue, $edit_pinyinDialogue, $edit_meaningDialogue, $edit_character, $edit_dialogue_id);
        } else {
            $sql = $con->prepare("UPDATE dialogues SET chinese_text = ?, pinyin_text = ?, meaning = ?, character_name = ? WHERE id = ?");
            $sql->bind_param("ssssi", $edit_dialogue, $edit_pinyinDialogue, $edit_meaningDialogue, $edit_character, $edit_dialogue_id);
        }
    }

    $sql->execute();
    $sql->close();

    header("Location: ../frontend/topic-content.php?id=$topik_id&section=dialogue");
    exit;
}

if (isset($_POST['add_dialogue'])) {
    $add_dialogue = $_POST['add_dialogue'];
    $add_pinyinDialogue = $_POST['add_pinyinDialogue'];
    $add_character = $_POST['add_character'];
    $add_meaningDialogue = $_POST['add_meaningDialogue'];

    $useScenarioSchema = hasScenarioSchema($con);
    $scenarioId = isset($_POST['scenario_id']) ? (int)$_POST['scenario_id'] : 0;
    if ($useScenarioSchema && $scenarioId <= 0) {
        $scenarioId = ensureDefaultScenario($con, $topik_id);
    }

    $lineNo = $useScenarioSchema ? nextLineNo($con, $scenarioId) : 0;

    if ($useScenarioSchema) {
        $audioName = $scenarioId . "-" . $lineNo . "-" . uniqid('', true) . ".mp3";
    } else {
        $audioName = time() . "-" . uniqid('', true) . ".mp3";
    }
    $destination = $folder . $audioName;

    if (isset($_FILES['add_audioDialogue']) && $_FILES['add_audioDialogue']['error'] == 0) {
        move_uploaded_file($_FILES['add_audioDialogue']['tmp_name'], $destination);
    } else {
        $destination = "";
    }

    if ($useScenarioSchema) {
        $insert = $con->prepare("INSERT INTO dialogues (topic_id, scenario_id, line_no, chinese_text, pinyin_text, meaning, character_name, audio_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("iiisssss", $topik_id, $scenarioId, $lineNo, $add_dialogue, $add_pinyinDialogue, $add_meaningDialogue, $add_character, $destination);
    } else {
        $insert = $con->prepare("INSERT INTO dialogues (topic_id, chinese_text, pinyin_text, meaning, character_name, audio_path) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("isssss", $topik_id, $add_dialogue, $add_pinyinDialogue, $add_meaningDialogue, $add_character, $destination);
    }
    $insert->execute();
    $insert->close();

    header("Location: ../frontend/topic-content.php?id=$topik_id&section=dialogue");
    exit;
}

if (isset($_GET['delete-id'])) {
    $id = (int)$_GET['delete-id'];

    $query = $con->prepare("SELECT audio_path FROM dialogues WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $data = $result ? $result->fetch_assoc() : null;
    $query->close();

    if ($data && !empty($data['audio_path']) && file_exists($data['audio_path'])) {
        unlink($data['audio_path']);
    }

    $del = $con->prepare("DELETE FROM dialogues WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();

    header("Location: ../frontend/topic-content.php?id=$topik_id&section=dialogue");
    exit;
}
?>

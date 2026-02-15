<?php
ob_start();
include "../config/db.php";
error_reporting(0);
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$type = isset($_POST['type']) ? $_POST['type'] : '';

// ─── DEBUG MODE — visit save_song.php?debug in browser to diagnose ────────
if (isset($_GET['debug'])) {
    ob_end_clean();
    header('Content-Type: text/plain');
    echo "=== SESSION ===\n";
    print_r($_SESSION);
    echo "\n=== POST ===\n";
    print_r($_POST);
    echo "\n=== FILES ===\n";
    print_r($_FILES);
    echo "\n=== DB CONNECTION ===\n";
    echo isset($conn) ? "OK (host: " . $conn->host_info . ")" : "FAILED";
    echo "\n\n=== SONGS TABLE COLUMNS ===\n";
    $r = $conn->query("SHOW COLUMNS FROM songs");
    if ($r) { while ($row = $r->fetch_assoc()) echo $row['Field'] . " (" . $row['Type'] . ")\n"; }
    else echo "ERROR: " . $conn->error;
    echo "\n=== upload_max_filesize: " . ini_get('upload_max_filesize') . " ===\n";
    echo "=== post_max_size: " . ini_get('post_max_size') . " ===\n";
    exit;
}

// ─── AUDIO SONG (MP3 + LRC) ───────────────────────────────────────────────
if ($type === 'audio') {

    $title    = isset($_POST['title'])    ? trim($_POST['title'])    : '';
    $subtitle = isset($_POST['subtitle']) ? trim($_POST['subtitle']) : '';

    if (empty($title)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Title is required.']);
        exit;
    }

    // ── Validate MP3 ──
    if (!isset($_FILES['mp3_file']) || $_FILES['mp3_file']['error'] !== UPLOAD_ERR_OK) {
        $err = isset($_FILES['mp3_file']) ? $_FILES['mp3_file']['error'] : 'no file';
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'MP3 upload failed (error code: ' . $err . ')']);
        exit;
    }

    $mp3    = $_FILES['mp3_file'];
    $mp3Ext = strtolower(pathinfo($mp3['name'], PATHINFO_EXTENSION));

    if ($mp3Ext !== 'mp3') {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Only MP3 files are allowed.']);
        exit;
    }
    if ($mp3['size'] > 30 * 1024 * 1024) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'MP3 file too large. Maximum size is 30MB.']);
        exit;
    }

    // ── Validate LRC ──
    if (!isset($_FILES['lrc_file']) || $_FILES['lrc_file']['error'] !== UPLOAD_ERR_OK) {
        $err = isset($_FILES['lrc_file']) ? $_FILES['lrc_file']['error'] : 'no file';
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'LRC upload failed (error code: ' . $err . ')']);
        exit;
    }

    $lrc    = $_FILES['lrc_file'];
    $lrcExt = strtolower(pathinfo($lrc['name'], PATHINFO_EXTENSION));

    if ($lrcExt !== 'lrc') {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Only LRC files are allowed.']);
        exit;
    }
    if ($lrc['size'] > 2 * 1024 * 1024) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'LRC file too large. Maximum 2MB.']);
        exit;
    }

    // ── Create upload directory ──
    $uploadDir = __DIR__ . '/../uploads/audio/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Cannot create uploads/audio/ folder. Check server permissions.']);
            exit;
        }
    }
    if (!is_writable($uploadDir)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'uploads/audio/ is not writable. Run: chmod 755 uploads/audio/']);
        exit;
    }

    // ── Save MP3 ──
    $mp3Filename = uniqid('song_', true) . '.mp3';
    $mp3Dest     = $uploadDir . $mp3Filename;
    if (!move_uploaded_file($mp3['tmp_name'], $mp3Dest)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to move MP3 file. Check folder permissions.']);
        exit;
    }

    // ── Save LRC ──
    $lrcFilename = uniqid('lrc_', true) . '.lrc';
    $lrcDest     = $uploadDir . $lrcFilename;
    if (!move_uploaded_file($lrc['tmp_name'], $lrcDest)) {
        unlink($mp3Dest);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to move LRC file. Check folder permissions.']);
        exit;
    }

    // ── Paths stored in DB ──
    $mp3DbPath = 'uploads/audio/' . $mp3Filename;
    $lrcDbPath = 'uploads/audio/' . $lrcFilename;
    $hasAudio  = 1;
    $adminId   = $_SESSION['user_id'];

    // ── Insert using your actual column names ──
    $stmt = $conn->prepare(
        "INSERT INTO songs (title, subtitle, audio_file, lrc_file, has_audio, user_id)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        unlink($mp3Dest);
        unlink($lrcDest);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'DB prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssis", $title, $subtitle, $mp3DbPath, $lrcDbPath, $hasAudio, $adminId);

    if ($stmt->execute()) {
        $stmt->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Song created successfully.']);
    } else {
        $dbErr = $stmt->error;
        $stmt->close();
        unlink($mp3Dest);
        unlink($lrcDest);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $dbErr]);
    }
    exit;
}

// ─── LYRICS-ONLY SONG ─────────────────────────────────────────────────────
if ($type === 'lyrics') {

    $title    = isset($_POST['title'])    ? trim($_POST['title'])    : '';
    $subtitle = isset($_POST['subtitle']) ? trim($_POST['subtitle']) : '';
    $partsRaw = isset($_POST['parts'])    ? $_POST['parts']          : '';

    if (empty($title)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Title is required.']);
        exit;
    }

    $parts = json_decode($partsRaw, true);
    if (!$parts || !is_array($parts)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid parts data.']);
        exit;
    }

    $partsJson = json_encode($parts, JSON_UNESCAPED_UNICODE);
    $adminId   = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "INSERT INTO songs (title, subtitle, parts, user_id) VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'DB prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssi", $title, $subtitle, $partsJson, $adminId);

    if ($stmt->execute()) {
        $stmt->close();
        ob_end_clean();
        echo json_encode(['success' => true, 'message' => 'Song created successfully.']);
    } else {
        $dbErr = $stmt->error;
        $stmt->close();
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $dbErr]);
    }
    exit;
}

// ─── Unknown type ─────────────────────────────────────────────────────────
ob_end_clean();
echo json_encode(['success' => false, 'message' => 'Unknown song type: "' . htmlspecialchars($type) . '"']);
exit;
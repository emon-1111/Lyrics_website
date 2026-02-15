<?php
include "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$type = isset($_POST['type']) ? $_POST['type'] : '';

// ─── AUDIO SONG (MP3 + LRC) ───────────────────────────────────────────────
if ($type === 'audio') {

    $title    = isset($_POST['title'])    ? trim($_POST['title'])    : '';
    $subtitle = isset($_POST['subtitle']) ? trim($_POST['subtitle']) : '';

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Title is required.']);
        exit;
    }

    // ── Validate MP3 ──
    if (!isset($_FILES['mp3_file']) || $_FILES['mp3_file']['error'] !== UPLOAD_ERR_OK) {
        $err = isset($_FILES['mp3_file']) ? $_FILES['mp3_file']['error'] : 'no file';
        echo json_encode(['success' => false, 'message' => 'MP3 upload failed (error ' . $err . ')']);
        exit;
    }

    $mp3 = $_FILES['mp3_file'];
    $allowedMp3Mime = ['audio/mpeg', 'audio/mp3', 'audio/x-mpeg-3'];
    $mp3Finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mp3Mime  = finfo_file($mp3Finfo, $mp3['tmp_name']);
    finfo_close($mp3Finfo);

    if (!in_array($mp3Mime, $allowedMp3Mime) && strtolower(pathinfo($mp3['name'], PATHINFO_EXTENSION)) !== 'mp3') {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only MP3 files are allowed.']);
        exit;
    }

    if ($mp3['size'] > 30 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'MP3 file too large. Maximum size is 30MB.']);
        exit;
    }

    // ── Validate LRC ──
    if (!isset($_FILES['lrc_file']) || $_FILES['lrc_file']['error'] !== UPLOAD_ERR_OK) {
        $err = isset($_FILES['lrc_file']) ? $_FILES['lrc_file']['error'] : 'no file';
        echo json_encode(['success' => false, 'message' => 'LRC upload failed (error ' . $err . ')']);
        exit;
    }

    $lrc = $_FILES['lrc_file'];
    $lrcExt = strtolower(pathinfo($lrc['name'], PATHINFO_EXTENSION));
    if ($lrcExt !== 'lrc') {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only LRC files are allowed.']);
        exit;
    }
    if ($lrc['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'LRC file too large. Maximum size is 2MB.']);
        exit;
    }

    // ── Create upload directory ──
    $uploadDir = '../uploads/audio/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // ── Save MP3 ──
    $mp3Filename  = uniqid('song_', true) . '.mp3';
    $mp3Dest      = $uploadDir . $mp3Filename;
    if (!move_uploaded_file($mp3['tmp_name'], $mp3Dest)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save MP3 file. Check folder permissions.']);
        exit;
    }

    // ── Read & Parse LRC ──
    $lrcContent = file_get_contents($lrc['tmp_name']);
    if ($lrcContent === false) {
        unlink($mp3Dest);
        echo json_encode(['success' => false, 'message' => 'Failed to read LRC file.']);
        exit;
    }

    $lrcLines = parseLrc($lrcContent);
    if (empty($lrcLines)) {
        unlink($mp3Dest);
        echo json_encode(['success' => false, 'message' => 'LRC file is empty or has no valid timestamped lines.']);
        exit;
    }

    // ── Save to database ──
    $mp3Path  = 'uploads/audio/' . $mp3Filename;
    $lrcJson  = json_encode($lrcLines, JSON_UNESCAPED_UNICODE);
    $songType = 'audio';

    $stmt = $conn->prepare(
        "INSERT INTO songs (title, subtitle, type, mp3_path, lrc_data, user_id) VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        // Try alternate column schema (in case table uses 'parts' instead of 'lrc_data')
        $stmt = $conn->prepare(
            "INSERT INTO songs (title, subtitle, type, mp3_path, parts, user_id) VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            unlink($mp3Dest);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
    }

    $adminId = $_SESSION['user_id'];
    $stmt->bind_param("sssssi", $title, $subtitle, $songType, $mp3Path, $lrcJson, $adminId);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Song created successfully.']);
    } else {
        $stmt->close();
        unlink($mp3Dest);
        echo json_encode(['success' => false, 'message' => 'Database insert failed: ' . $conn->error]);
    }
    exit;
}

// ─── LYRICS-ONLY SONG ─────────────────────────────────────────────────────
if ($type === 'lyrics') {

    $title    = isset($_POST['title'])    ? trim($_POST['title'])    : '';
    $subtitle = isset($_POST['subtitle']) ? trim($_POST['subtitle']) : '';
    $partsRaw = isset($_POST['parts'])    ? $_POST['parts']          : '';

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Title is required.']);
        exit;
    }

    $parts = json_decode($partsRaw, true);
    if (!$parts || !is_array($parts)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parts data.']);
        exit;
    }

    $partsJson = json_encode($parts, JSON_UNESCAPED_UNICODE);
    $songType  = 'lyrics';
    $adminId   = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "INSERT INTO songs (title, subtitle, type, parts, user_id) VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssi", $title, $subtitle, $songType, $partsJson, $adminId);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Song created successfully.']);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Database insert failed: ' . $conn->error]);
    }
    exit;
}

// ─── Unknown type ─────────────────────────────────────────────────────────
echo json_encode(['success' => false, 'message' => 'Unknown song type.']);
exit;

// ─── LRC Parser ───────────────────────────────────────────────────────────
/**
 * Parse an LRC file into an array of ['time' => float_seconds, 'text' => string]
 * Supports standard [mm:ss.xx] and [mm:ss.xxx] timestamps.
 */
function parseLrc(string $content): array {
    $lines  = explode("\n", str_replace("\r\n", "\n", $content));
    $result = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        // Match all timestamps on the line
        if (preg_match_all('/\[(\d{1,3}):(\d{2})\.(\d{2,3})\]/', $line, $matches, PREG_SET_ORDER)) {
            // Get the lyric text (everything after the last timestamp)
            $text = preg_replace('/^\[(\d{1,3}):(\d{2})\.(\d{2,3})\]/', '', $line);
            $text = trim($text);

            // Skip metadata tags like [ar:], [ti:], [al:], [offset:]
            if (preg_match('/^\[[a-z]+:/i', $line)) continue;

            foreach ($matches as $m) {
                $minutes    = (int)$m[1];
                $seconds    = (int)$m[2];
                $centisecs  = strlen($m[3]) === 3 ? (int)$m[3] / 10 : (int)$m[3];
                $totalSecs  = $minutes * 60 + $seconds + ($centisecs / 100);

                $result[] = [
                    'time' => $totalSecs,
                    'text' => $text,
                ];
            }
        }
    }

    // Sort by time
    usort($result, fn($a, $b) => $a['time'] <=> $b['time']);

    return $result;
}
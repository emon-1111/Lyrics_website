<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Song with Audio - Admin</title>
    <link rel="stylesheet" href="../frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <style>
        .create-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #b5b5b5;
            text-decoration: none;
            margin-bottom: 30px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #fff;
        }

        .create-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .create-header h1 {
            font-size: 32px;
            color: #fff;
            margin-bottom: 10px;
        }

        .create-header p {
            color: #b5b5b5;
            font-size: 16px;
        }

        .create-form {
            background: linear-gradient(180deg, #111, #0f0f0f);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 40px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            color: #b5b5b5;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            background: #1a1a1a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 14px 16px;
            color: #fff;
            font-size: 15px;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4ade80;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .file-upload-area {
            border: 2px dashed rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .file-upload-area:hover,
        .file-upload-area.dragover {
            border-color: #4ade80;
            background: rgba(74, 222, 128, 0.05);
        }

        .file-upload-area input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .file-upload-icon {
            font-size: 36px;
            color: #4ade80;
            margin-bottom: 12px;
        }

        .file-upload-text {
            color: #b5b5b5;
            font-size: 15px;
        }

        .file-upload-text span {
            color: #4ade80;
            font-weight: 600;
        }

        .file-upload-hint {
            font-size: 13px;
            color: #666;
            margin-top: 6px;
        }

        .file-selected-name {
            margin-top: 10px;
            font-size: 14px;
            color: #4ade80;
            display: none;
        }

        .form-divider {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            margin: 30px 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            border: none;
            border-radius: 10px;
            color: #000;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 222, 128, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .upload-progress {
            display: none;
            margin-top: 20px;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
        }

        .progress-bar {
            height: 6px;
            background: linear-gradient(135deg, #4ade80, #22c55e);
            width: 0%;
            transition: width 0.3s;
        }

        .progress-text {
            text-align: center;
            color: #b5b5b5;
            font-size: 13px;
            padding: 8px;
        }

        .error-msg {
            display: none;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            padding: 12px 16px;
            color: #f87171;
            font-size: 14px;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .create-form {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="create-container">
        <a href="create_song_choice.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>

        <div class="create-header">
            <h1><i class="fa-solid fa-headphones"></i> Create Song with Audio</h1>
            <p>Upload an MP3 and LRC file to create a synced lyrics experience</p>
        </div>

        <div class="error-msg" id="errorMsg"></div>

        <form class="create-form" id="audioSongForm" action="save_song.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="type" value="audio">

            <div class="form-row">
                <div class="form-group">
                    <label for="title">Song Title *</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Bohemian Rhapsody" required>
                </div>
                <div class="form-group">
                    <label for="subtitle">Artist / Subtitle</label>
                    <input type="text" id="subtitle" name="subtitle" placeholder="e.g. Queen">
                </div>
            </div>

            <hr class="form-divider">

            <!-- MP3 Upload -->
            <div class="form-group">
                <label>MP3 Audio File *</label>
                <div class="file-upload-area" id="mp3DropArea">
                    <input type="file" name="mp3_file" id="mp3File" accept=".mp3,audio/mpeg" required>
                    <div class="file-upload-icon">
                        <i class="fa-solid fa-music"></i>
                    </div>
                    <div class="file-upload-text">
                        <span>Click to upload</span> or drag &amp; drop
                    </div>
                    <div class="file-upload-hint">MP3 files only · Max 30MB</div>
                    <div class="file-selected-name" id="mp3FileName"></div>
                </div>
            </div>

            <!-- LRC Upload -->
            <div class="form-group">
                <label>LRC Lyrics File *</label>
                <div class="file-upload-area" id="lrcDropArea">
                    <input type="file" name="lrc_file" id="lrcFile" accept=".lrc,text/plain" required>
                    <div class="file-upload-icon" style="color:#667eea;">
                        <i class="fa-solid fa-align-left"></i>
                    </div>
                    <div class="file-upload-text">
                        <span style="color:#667eea;">Click to upload</span> or drag &amp; drop
                    </div>
                    <div class="file-upload-hint">LRC files only · Timestamped lyrics format</div>
                    <div class="file-selected-name" id="lrcFileName" style="color:#667eea;"></div>
                </div>
            </div>

            <div class="upload-progress" id="uploadProgress">
                <div class="progress-bar" id="progressBar"></div>
                <div class="progress-text" id="progressText">Uploading...</div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                Create Song
            </button>
        </form>
    </div>

    <script>
        // File input display
        function setupFileInput(inputId, fileNameId, dropAreaId) {
            const input = document.getElementById(inputId);
            const nameDisplay = document.getElementById(fileNameId);
            const dropArea = document.getElementById(dropAreaId);

            input.addEventListener('change', () => {
                if (input.files[0]) {
                    nameDisplay.textContent = '✓ ' + input.files[0].name;
                    nameDisplay.style.display = 'block';
                    dropArea.style.borderStyle = 'solid';
                }
            });

            dropArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropArea.classList.add('dragover');
            });

            dropArea.addEventListener('dragleave', () => {
                dropArea.classList.remove('dragover');
            });

            dropArea.addEventListener('drop', (e) => {
                e.preventDefault();
                dropArea.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                if (file) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    nameDisplay.textContent = '✓ ' + file.name;
                    nameDisplay.style.display = 'block';
                    dropArea.style.borderStyle = 'solid';
                }
            });
        }

        setupFileInput('mp3File', 'mp3FileName', 'mp3DropArea');
        setupFileInput('lrcFile', 'lrcFileName', 'lrcDropArea');

        // Form submission with progress
        document.getElementById('audioSongForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const title = document.getElementById('title').value.trim();
            const mp3 = document.getElementById('mp3File').files[0];
            const lrc = document.getElementById('lrcFile').files[0];
            const errorMsg = document.getElementById('errorMsg');

            // Validate
            if (!title) {
                errorMsg.textContent = 'Please enter a song title.';
                errorMsg.style.display = 'block';
                return;
            }
            if (!mp3) {
                errorMsg.textContent = 'Please upload an MP3 file.';
                errorMsg.style.display = 'block';
                return;
            }
            if (!lrc) {
                errorMsg.textContent = 'Please upload an LRC file.';
                errorMsg.style.display = 'block';
                return;
            }
            if (!mp3.name.toLowerCase().endsWith('.mp3')) {
                errorMsg.textContent = 'Audio file must be an MP3.';
                errorMsg.style.display = 'block';
                return;
            }
            if (mp3.size > 30 * 1024 * 1024) {
                errorMsg.textContent = 'MP3 file is too large. Maximum size is 30MB.';
                errorMsg.style.display = 'block';
                return;
            }
            if (!lrc.name.toLowerCase().endsWith('.lrc')) {
                errorMsg.textContent = 'Lyrics file must be an LRC file.';
                errorMsg.style.display = 'block';
                return;
            }

            errorMsg.style.display = 'none';

            const submitBtn = document.getElementById('submitBtn');
            const progressDiv = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');

            submitBtn.disabled = true;
            progressDiv.style.display = 'block';

            const formData = new FormData(this);

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = pct + '%';
                    progressText.textContent = 'Uploading... ' + pct + '%';
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            progressText.textContent = 'Song created successfully! Redirecting...';
                            progressBar.style.width = '100%';
                            setTimeout(() => {
                                window.location.href = 'song.php';
                            }, 1000);
                        } else {
                            errorMsg.textContent = res.message || 'Failed to create song.';
                            errorMsg.style.display = 'block';
                            submitBtn.disabled = false;
                            progressDiv.style.display = 'none';
                        }
                    } catch (err) {
                        errorMsg.textContent = 'Unexpected server response.';
                        errorMsg.style.display = 'block';
                        submitBtn.disabled = false;
                        progressDiv.style.display = 'none';
                    }
                } else {
                    errorMsg.textContent = 'Upload failed. Server error ' + xhr.status;
                    errorMsg.style.display = 'block';
                    submitBtn.disabled = false;
                    progressDiv.style.display = 'none';
                }
            });

            xhr.addEventListener('error', () => {
                errorMsg.textContent = 'Network error. Please try again.';
                errorMsg.style.display = 'block';
                submitBtn.disabled = false;
                progressDiv.style.display = 'none';
            });

            xhr.open('POST', 'save_song.php');
            xhr.send(formData);
        });
    </script>
</body>
</html>
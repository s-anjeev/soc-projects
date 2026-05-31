<?php
declare(strict_types=1);

define('UPLOAD_DIR', __DIR__ . '\\uploads\\');
define('MAX_SIZE',   100 * 1024 * 1024);

function respond(bool $ok, string $message, array $extra = []): void {
    http_response_code($ok ? 200 : 400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        array_merge(['success' => $ok, 'message' => $message], $extra),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function php_upload_error_message(int $code): string {
    $map = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds upload_max_filesize in php.ini ('
                                  . ini_get('upload_max_filesize') . '). '
                                  . 'Raise it in C:\\xampp\\php\\php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds MAX_FILE_SIZE sent with the form.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded. Please retry.',
        UPLOAD_ERR_NO_TMP_DIR => 'No temp folder configured. Check upload_tmp_dir in php.ini.',
        UPLOAD_ERR_CANT_WRITE => 'PHP could not write to temp folder. '
                                  . 'Check NTFS permissions on C:\\xampp\\tmp.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
    ];
    return $map[$code] ?? 'Unknown PHP upload error (code ' . $code . ').';
}

// ── POST: handle the upload ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        respond(false, 'No file was included. Ensure the field is named "file" '
            . 'and the form has enctype="multipart/form-data".');
    }

    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        respond(false, php_upload_error_message($file['error']));
    }

    if ($file['size'] === 0) {
        respond(false, 'The uploaded file is empty (0 bytes).');
    }

    if ($file['size'] > MAX_SIZE) {
        respond(false, sprintf(
            'File too large: %.2f MB. Maximum allowed is %d MB.',
            $file['size'] / 1048576,
            MAX_SIZE / 1048576
        ));
    }

    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true)) {
            respond(false,
                'Could not create uploads\\ directory. '
                . 'Grant "Modify" NTFS permission on htdocs\\ to the SYSTEM account.');
        }
    }

    if (!is_writable(UPLOAD_DIR)) {
        respond(false,
            'uploads\\ exists but is not writable by Apache. '
            . 'Right-click uploads\\ → Properties → Security → '
            . 'grant Modify to SYSTEM (or the httpd.exe user).');
    }

    $original  = basename($file['name']);
    $name_part = pathinfo($original, PATHINFO_FILENAME);
    $ext_part  = pathinfo($original, PATHINFO_EXTENSION);
    $safe_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name_part);
    $safe_ext  = preg_replace('/[^a-zA-Z0-9]/',    '',  $ext_part);
    $safe_name = $safe_name !== '' ? substr($safe_name, 0, 80) : 'file';
    $dest_name = $safe_name . '_' . bin2hex(random_bytes(5))
               . ($safe_ext !== '' ? '.' . $safe_ext : '');
    $dest_path = UPLOAD_DIR . $dest_name;

    if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
        respond(false,
            'move_uploaded_file() failed. Common XAMPP causes: '
            . '(1) uploads\\ is on a different drive than C:\\xampp\\tmp — cross-volume moves fail; '
            . '(2) antivirus quarantined the temp file; '
            . '(3) insufficient NTFS write permission on uploads\\.');
    }

    respond(true, 'File uploaded successfully.', [
        'saved_as'   => $dest_name,
        'original'   => htmlspecialchars($original, ENT_QUOTES, 'UTF-8'),
        'size_bytes' => filesize($dest_path),
        'size_mb'    => round(filesize($dest_path) / 1048576, 3),
        'path'       => str_replace('\\', '/', realpath($dest_path)),
    ]);
}
// ── GET (or any other method): show the upload form ─────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>File Upload</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: Segoe UI, sans-serif;
      background: #f4f6f9;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .card {
      background: #fff;
      border: 1px solid #dde2ea;
      border-radius: 10px;
      padding: 2rem 2.2rem;
      width: 100%;
      max-width: 480px;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    h2  { font-size: 1.15rem; font-weight: 600; color: #1a1a2e; margin-bottom: 1.4rem; }
    label { display: block; font-size: .85rem; color: #555; margin-bottom: .4rem; }

    .drop-zone {
      border: 2px dashed #c5cfe0;
      border-radius: 8px;
      padding: 2rem 1rem;
      text-align: center;
      cursor: pointer;
      transition: border-color .2s, background .2s;
      margin-bottom: 1rem;
      color: #7a8499;
      font-size: .9rem;
    }
    .drop-zone.dragover { border-color: #1a56b0; background: #eef3fb; }
    .drop-zone input    { display: none; }
    .drop-zone .icon    { font-size: 2rem; margin-bottom: .5rem; }
    #file-name          { font-size: .82rem; color: #1a56b0; margin-top: .4rem; }

    button {
      width: 100%;
      padding: .65rem;
      background: #1a56b0;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: .95rem;
      cursor: pointer;
      transition: background .2s;
    }
    button:hover    { background: #154496; }
    button:disabled { background: #9ab2d8; cursor: not-allowed; }

    #progress-wrap {
      display: none;
      margin-top: 1rem;
      background: #e8edf5;
      border-radius: 4px;
      overflow: hidden;
      height: 8px;
    }
    #progress-bar {
      height: 100%;
      width: 0;
      background: #1a56b0;
      transition: width .15s;
    }

    #result {
      display: none;
      margin-top: 1.1rem;
      padding: .85rem 1rem;
      border-radius: 6px;
      font-size: .83rem;
      font-family: Consolas, monospace;
      white-space: pre-wrap;
      word-break: break-all;
    }
    .ok  { background: #eaf3de; border: 1px solid #85c15a; color: #27500a; }
    .err { background: #fcebeb; border: 1px solid #e07070; color: #6b1414; }

    .meta { font-size: .78rem; color: #888; margin-top: 1.2rem; text-align: center; }
  </style>
</head>
<body>
<div class="card">
  <h2>&#128193; Upload a file</h2>

  <form id="uploadForm" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAX_SIZE ?>">

    <label>Select or drag a file</label>
    <div class="drop-zone" id="dropZone">
      <div class="icon">&#8682;</div>
      <div>Drop file here or <strong>click to browse</strong></div>
      <div id="file-name">No file chosen</div>
      <input type="file" id="fileInput" name="file">
    </div>

    <button type="submit" id="submitBtn" disabled>Upload</button>

    <div id="progress-wrap"><div id="progress-bar"></div></div>
    <div id="result"></div>
  </form>

  <p class="meta">Max size: <?= MAX_SIZE / 1048576 ?> MB &nbsp;·&nbsp; All file types accepted</p>
</div>

<script>
  const dropZone   = document.getElementById('dropZone');
  const fileInput  = document.getElementById('fileInput');
  const fileLabel  = document.getElementById('file-name');
  const submitBtn  = document.getElementById('submitBtn');
  const form       = document.getElementById('uploadForm');
  const result     = document.getElementById('result');
  const progressWrap = document.getElementById('progress-wrap');
  const progressBar  = document.getElementById('progress-bar');

  // Click on drop zone → open file picker
  dropZone.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
      fileLabel.textContent = fileInput.files[0].name;
      submitBtn.disabled = false;
    }
  });

  // Drag-and-drop
  dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
  dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('dragover'));
  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
      fileInput.files = e.dataTransfer.files;
      fileLabel.textContent = e.dataTransfer.files[0].name;
      submitBtn.disabled = false;
    }
  });

  // Submit via XHR so we get progress events
  form.addEventListener('submit', e => {
    e.preventDefault();
    result.style.display = 'none';
    submitBtn.disabled   = true;
    progressWrap.style.display = 'block';
    progressBar.style.width    = '0%';

    const xhr  = new XMLHttpRequest();
    const data = new FormData(form);

    xhr.upload.addEventListener('progress', ev => {
      if (ev.lengthComputable) {
        progressBar.style.width = Math.round((ev.loaded / ev.total) * 100) + '%';
      }
    });

    xhr.addEventListener('load', () => {
      progressWrap.style.display = 'none';
      submitBtn.disabled = false;
      let json;
      try { json = JSON.parse(xhr.responseText); }
      catch { json = { success: false, message: 'Server returned non-JSON response:\n' + xhr.responseText }; }
      result.className     = json.success ? 'ok' : 'err';
      result.style.display = 'block';
      result.textContent   = JSON.stringify(json, null, 2);
    });

    xhr.addEventListener('error', () => {
      progressWrap.style.display = 'none';
      submitBtn.disabled = false;
      result.className     = 'err';
      result.style.display = 'block';
      result.textContent   = 'Network error — could not reach the server.';
    });

    xhr.open('POST', 'upload.php');
    xhr.send(data);
  });
</script>
</body>
</html>
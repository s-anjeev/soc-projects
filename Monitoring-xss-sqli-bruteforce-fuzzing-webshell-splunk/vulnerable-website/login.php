<?php
// ============================================================
//  LAB FILE — SQLi INTENTIONALLY ABSENT (for practice/demo)
//  DO NOT deploy to production
// ============================================================

$message      = "";
$login_failed = false;
$redirect     = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    $conn = new mysqli("localhost", "root", "", "database");

    if ($conn->connect_error) {
        $message      = "DB connection failed: " . $conn->connect_error;
        $login_failed = true;
        http_response_code(500);
    } else {
        // ⚠️  NO sanitisation — intentional for SQLi lab
        $query  = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $redirect = true;
        } else {
            $message      = "fail";
            $login_failed = true;
            http_response_code(401);   // ← 401 on failed auth
        }
        $conn->close();
    }
}

if ($redirect) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" <?= $login_failed ? 'data-state="failed"' : '' ?>>
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — Lab</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono:ital,wght@0,400;1,300&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── base palette (default / success state) ── */
    :root {
      --bg:      #0a0a0f;
      --surface: #111118;
      --border:  #2a2a3a;
      --accent:  #e63946;
      --accent2: #ff6b6b;
      --orb:     rgba(230, 57, 70, .18);
      --grid-c:  rgba(230, 57, 70, .07);
      --text:    #e8e8f0;
      --muted:   #666680;
      --mono:    'DM Mono', monospace;
      --sans:    'Syne', sans-serif;
    }

    /* ── 401 failure theme — amber ── */
    [data-state="failed"] {
      --accent:  #ff9f1c;
      --accent2: #ffbf69;
      --orb:     rgba(255, 159, 28, .18);
      --grid-c:  rgba(255, 159, 28, .07);
      --surface: #120e08;
      --border:  #3a2a10;
    }

    html, body {
      height: 100%;
      background: var(--bg);
      color: var(--text);
      font-family: var(--sans);
      overflow: hidden;
    }

    /* ── animated grid ── */
    body::before {
      content: '';
      position: fixed; inset: 0;
      background-image:
        linear-gradient(var(--grid-c) 1px, transparent 1px),
        linear-gradient(90deg, var(--grid-c) 1px, transparent 1px);
      background-size: 40px 40px;
      animation: gridDrift 20s linear infinite;
      pointer-events: none;
      transition: background-image .6s ease;
    }
    @keyframes gridDrift { to { background-position: 40px 40px; } }

    /* ── glowing orb ── */
    body::after {
      content: '';
      position: fixed;
      width: 600px; height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, var(--orb) 0%, transparent 70%);
      top: -150px; right: -150px;
      pointer-events: none;
      animation: orbPulse 6s ease-in-out infinite alternate;
      transition: background .6s ease;
    }
    @keyframes orbPulse { to { transform: scale(1.15) translate(-30px, 30px); opacity: .6; } }

    /* ── layout ── */
    .wrapper {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 1;
    }

    .card {
      width: 420px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 48px 40px 40px;
      position: relative;
      animation: cardIn .5s cubic-bezier(.22, 1, .36, 1) both;
      transition: background .5s ease, border-color .5s ease;
    }
    @keyframes cardIn { from { opacity: 0; transform: translateY(24px); } }

    /* shake on failure */
    .card.shake {
      animation: shake .4s cubic-bezier(.36, .07, .19, .97) both;
    }
    @keyframes shake {
      10%, 90%  { transform: translateX(-2px); }
      20%, 80%  { transform: translateX(4px);  }
      30%, 50%, 70% { transform: translateX(-6px); }
      40%, 60%  { transform: translateX(6px);  }
    }

    /* top accent bar */
    .card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), var(--accent2), transparent);
      border-radius: 4px 4px 0 0;
      transition: background .5s ease;
    }

    /* ── status indicator strip ── */
    .status-strip {
      position: absolute;
      top: 12px; right: 14px;
      font-family: var(--mono);
      font-size: 10px;
      letter-spacing: .1em;
      color: var(--muted);
      transition: color .4s;
    }
    [data-state="failed"] .status-strip {
      color: var(--accent2);
    }

    /* ── lab badge ── */
    .lab-badge {
      font-family: var(--mono);
      font-size: 10px;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: var(--accent);
      background: rgba(230, 57, 70, .1);
      border: 1px solid rgba(230, 57, 70, .3);
      border-radius: 2px;
      padding: 3px 8px;
      display: inline-block;
      margin-bottom: 20px;
      transition: color .5s, background .5s, border-color .5s;
    }
    [data-state="failed"] .lab-badge {
      background: rgba(255, 159, 28, .1);
      border-color: rgba(255, 159, 28, .3);
    }

    h1 {
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -.02em;
      margin-bottom: 6px;
    }
    .sub {
      font-family: var(--mono);
      font-size: 12px;
      color: var(--muted);
      margin-bottom: 36px;
    }

    /* ── form ── */
    .field { margin-bottom: 20px; }

    label {
      display: block;
      font-family: var(--mono);
      font-size: 11px;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 8px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      background: #0d0d14;
      border: 1px solid var(--border);
      border-radius: 3px;
      color: var(--text);
      font-family: var(--mono);
      font-size: 14px;
      padding: 12px 14px;
      outline: none;
      transition: border-color .2s, box-shadow .2s, background .5s;
    }
    [data-state="failed"] input[type="text"],
    [data-state="failed"] input[type="password"] {
      background: #100c06;
    }
    input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(230, 57, 70, .12);
    }
    [data-state="failed"] input:focus {
      box-shadow: 0 0 0 3px rgba(255, 159, 28, .12);
    }

    /* ── submit button ── */
    button[type="submit"] {
      width: 100%;
      margin-top: 8px;
      padding: 13px;
      background: var(--accent);
      color: #fff;
      font-family: var(--sans);
      font-size: 14px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      border: none;
      border-radius: 3px;
      cursor: pointer;
      transition: background .2s, transform .1s;
    }
    button:hover  { background: var(--accent2); }
    button:active { transform: scale(.98); }

    /* ── message box ── */
    .msg {
      margin-top: 20px;
      padding: 12px 16px;
      border-radius: 3px;
      font-family: var(--mono);
      font-size: 13px;
      animation: fadeIn .3s ease both;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } }

    .msg.fail {
      background: rgba(255, 159, 28, .08);
      border: 1px solid rgba(255, 159, 28, .3);
      color: var(--accent2);
    }

    /* HTTP badge inside message */
    .http-badge {
      font-family: var(--mono);
      font-size: 11px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 2px;
      background: rgba(255, 159, 28, .2);
      color: var(--accent2);
      white-space: nowrap;
      flex-shrink: 0;
      margin-top: 1px;
    }

    .msg-text { line-height: 1.5; }

    /* ── footer note ── */
    .note {
      margin-top: 28px;
      font-family: var(--mono);
      font-size: 10px;
      color: #333348;
      text-align: center;
      line-height: 1.7;
    }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="card <?= $login_failed ? 'shake' : '' ?>" id="card">

    <span class="status-strip">
      <?= $login_failed ? 'HTTP 401' : 'HTTP 200' ?>
    </span>

    <span class="lab-badge">⚠ Lab Environment</span>
    <h1>Sign In</h1>
    <p class="sub">// sqli_lab · no sanitisation</p>

    <form method="POST" action="">
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               placeholder="enter username" autocomplete="off" required
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"/>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="enter password" required/>
      </div>
      <button type="submit">Authenticate →</button>
    </form>

    <?php if ($message === "fail"): ?>
      <div class="msg fail">
        <span class="http-badge">401</span>
        <span class="msg-text">Authentication failed — credentials not recognised.</span>
      </div>
    <?php elseif ($message): ?>
      <div class="msg fail">
        <span class="http-badge">500</span>
        <span class="msg-text"><?= htmlspecialchars($message) ?></span>
      </div>
    <?php endif; ?>

    <p class="note">
      DB: localhost / database / users<br/>
      Fields: username · password
    </p>
  </div>
</div>

<script>
  // Re-trigger shake if card already has the class on load
  // (prevents CSS animation from not replaying on page reload)
  const card = document.getElementById('card');
  if (card.classList.contains('shake')) {
    card.classList.remove('shake');
    void card.offsetWidth; // reflow
    card.classList.add('shake');
  }
</script>
</body>
</html>
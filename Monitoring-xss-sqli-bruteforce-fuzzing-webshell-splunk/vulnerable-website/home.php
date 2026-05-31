<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home — Welcome</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:     #06060e;
      --accent: #e63946;
      --gold:   #ffd166;
      --teal:   #06d6a0;
      --blue:   #118ab2;
      --text:   #e8e8f0;
      --muted:  #555570;
      --mono:   'DM Mono', monospace;
      --sans:   'Syne', sans-serif;
    }

    html, body { height: 100%; background: var(--bg); overflow: hidden; }

    /* ── canvas fills the whole bg ── */
    #art { position: fixed; inset: 0; z-index: 0; }

    /* ── UI overlay ── */
    .ui {
      position: fixed; inset: 0;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      pointer-events: none;
    }

    .badge {
      font-family: var(--mono);
      font-size: 11px;
      letter-spacing: .15em;
      text-transform: uppercase;
      color: var(--teal);
      background: rgba(6,214,160,.1);
      border: 1px solid rgba(6,214,160,.3);
      border-radius: 2px;
      padding: 4px 12px;
      margin-bottom: 20px;
      animation: fadeUp .6s .2s both;
    }

    h1 {
      font-family: var(--sans);
      font-size: clamp(36px, 6vw, 80px);
      font-weight: 800;
      letter-spacing: -.03em;
      color: var(--text);
      text-align: center;
      line-height: 1;
      animation: fadeUp .6s .35s both;
    }
    h1 span { color: var(--accent); }

    .sub {
      margin-top: 16px;
      font-family: var(--mono);
      font-size: 13px;
      color: var(--muted);
      animation: fadeUp .6s .5s both;
    }

    /* logout link */
    .logout {
      position: fixed;
      top: 24px; right: 28px;
      z-index: 2;
      font-family: var(--mono);
      font-size: 11px;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--muted);
      text-decoration: none;
      pointer-events: all;
      border-bottom: 1px solid transparent;
      transition: color .2s, border-color .2s;
    }
    .logout:hover { color: var(--accent); border-color: var(--accent); }

    /* version tag bottom-left */
    .ver {
      position: fixed;
      bottom: 20px; left: 24px;
      font-family: var(--mono);
      font-size: 10px;
      color: #222236;
      letter-spacing: .08em;
      z-index: 2;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<canvas id="art"></canvas>

<div class="ui">
  <span class="badge">✓ Authentication Successful</span>
  <h1>Welcome<br/><span>Home.</span></h1>
  <p class="sub">// access granted · session active</p>
</div>

<a class="logout" href="login.php">⏎ logout</a>
<span class="ver">sqli_lab · home.php</span>

<script>
// ── Generative art: particle field with flow lines ──────────────
const canvas = document.getElementById('art');
const ctx    = canvas.getContext('2d');

let W, H, particles, frame = 0;

const COLORS = ['#e63946','#ffd166','#06d6a0','#118ab2','#a8dadc'];

function resize() {
  W = canvas.width  = window.innerWidth;
  H = canvas.height = window.innerHeight;
  init();
}

// Simplex-like noise approximation (2D value noise)
function noise(x, y, seed = 0) {
  const ix = Math.floor(x), iy = Math.floor(y);
  const fx = x - ix, fy = y - iy;
  const u  = fx * fx * (3 - 2 * fx);
  const v  = fy * fy * (3 - 2 * fy);
  const r  = (n) => {
    let h = (n ^ seed) * 2654435761 >>> 0;
    h = Math.imul(h ^ (h >>> 16), 0x45d9f3b) >>> 0;
    return (h / 4294967296) * 2 - 1;
  };
  return (
    r(ix     + iy     * 57) * (1-u) * (1-v) +
    r(ix + 1 + iy     * 57) *    u  * (1-v) +
    r(ix     + (iy+1) * 57) * (1-u) *    v  +
    r(ix + 1 + (iy+1) * 57) *    u  *    v
  );
}

function flowAngle(x, y, t) {
  return noise(x * 0.003, y * 0.003, t | 0) * Math.PI * 4;
}

class Particle {
  constructor() { this.reset(true); }
  reset(fresh) {
    this.x     = Math.random() * W;
    this.y     = Math.random() * H;
    this.speed = 0.8 + Math.random() * 1.4;
    this.size  = 0.6 + Math.random() * 1.6;
    this.color = COLORS[Math.floor(Math.random() * COLORS.length)];
    this.alpha = fresh ? Math.random() : 0;
    this.life  = 0;
    this.maxLife = 180 + Math.random() * 260;
    this.trail = [];
  }
  update(t) {
    const angle = flowAngle(this.x, this.y, t * 0.004);
    this.x += Math.cos(angle) * this.speed;
    this.y += Math.sin(angle) * this.speed;
    this.trail.push({ x: this.x, y: this.y });
    if (this.trail.length > 18) this.trail.shift();

    this.life++;
    const lifeRatio = this.life / this.maxLife;
    this.alpha = lifeRatio < 0.15
      ? lifeRatio / 0.15
      : lifeRatio > 0.8
        ? 1 - (lifeRatio - 0.8) / 0.2
        : 1;

    if (
      this.life >= this.maxLife ||
      this.x < -10 || this.x > W + 10 ||
      this.y < -10 || this.y > H + 10
    ) this.reset(false);
  }
  draw() {
    if (this.trail.length < 2) return;
    ctx.save();
    ctx.globalAlpha = this.alpha * 0.7;
    ctx.strokeStyle = this.color;
    ctx.lineWidth   = this.size;
    ctx.lineCap     = 'round';
    ctx.beginPath();
    ctx.moveTo(this.trail[0].x, this.trail[0].y);
    for (let i = 1; i < this.trail.length; i++) {
      ctx.lineTo(this.trail[i].x, this.trail[i].y);
    }
    ctx.stroke();
    // dot at head
    ctx.globalAlpha = this.alpha;
    ctx.fillStyle = this.color;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size * 1.2, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  }
}

function init() {
  const count = Math.floor((W * H) / 3800);
  particles = Array.from({ length: count }, () => new Particle());
}

function loop() {
  // fade trail — semi-transparent clear
  ctx.fillStyle = 'rgba(6,6,14,0.18)';
  ctx.fillRect(0, 0, W, H);

  for (const p of particles) { p.update(frame); p.draw(); }

  // occasional star-burst at random point
  if (frame % 90 === 0) {
    const sx = Math.random() * W;
    const sy = Math.random() * H;
    const grad = ctx.createRadialGradient(sx, sy, 0, sx, sy, 60);
    grad.addColorStop(0, 'rgba(230,57,70,.25)');
    grad.addColorStop(1, 'transparent');
    ctx.save();
    ctx.globalAlpha = .5;
    ctx.fillStyle = grad;
    ctx.beginPath();
    ctx.arc(sx, sy, 60, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  }

  frame++;
  requestAnimationFrame(loop);
}

window.addEventListener('resize', resize);
resize();
loop();
</script>
</body>
</html>

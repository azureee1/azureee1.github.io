/* ==========================================================================
   FIQIH SANTOSO — CYBER TECH PORTFOLIO
   Interactive Cyber Network Canvas & Matrix Particle System
   ========================================================================== */

class CyberParticleNetwork {
  constructor(canvasId) {
    this.canvas = document.getElementById(canvasId);
    if (!this.canvas) return;
    this.ctx = this.canvas.getContext('2d');
    this.particles = [];
    this.numParticles = 75;
    this.maxDistance = 140;
    this.mouse = { x: null, y: null, radius: 150 };
    this.mode = 'network'; // 'network' or 'matrix'
    this.matrixColumns = [];
    this.fontSize = 14;

    this.init();
  }

  init() {
    this.resize();
    window.addEventListener('resize', () => this.resize());
    window.addEventListener('mousemove', (e) => {
      this.mouse.x = e.clientX;
      this.mouse.y = e.clientY;
    });

    window.addEventListener('mouseout', () => {
      this.mouse.x = null;
      this.mouse.y = null;
    });

    this.createParticles();
    this.initMatrix();
    this.animate();
  }

  resize() {
    this.width = this.canvas.width = window.innerWidth;
    this.height = this.canvas.height = window.innerHeight;
    this.numParticles = Math.floor((this.width * this.height) / 16000);
    this.createParticles();
    this.initMatrix();
  }

  createParticles() {
    this.particles = [];
    for (let i = 0; i < this.numParticles; i++) {
      this.particles.push({
        x: Math.random() * this.width,
        y: Math.random() * this.height,
        vx: (Math.random() - 0.5) * 0.8,
        vy: (Math.random() - 0.5) * 0.8,
        size: Math.random() * 2 + 1,
        color: Math.random() > 0.3 ? '#00f3ff' : '#00ff66'
      });
    }
  }

  initMatrix() {
    const columns = Math.floor(this.width / this.fontSize);
    this.matrixColumns = [];
    for (let i = 0; i < columns; i++) {
      this.matrixColumns[i] = Math.floor(Math.random() * -100);
    }
  }

  setMode(mode) {
    this.mode = mode;
  }

  animate() {
    requestAnimationFrame(() => this.animate());

    if (this.mode === 'network') {
      this.drawNetwork();
    } else {
      this.drawMatrix();
    }
  }

  drawNetwork() {
    this.ctx.clearRect(0, 0, this.width, this.height);

    // Update & draw particles
    for (let i = 0; i < this.particles.length; i++) {
      const p = this.particles[i];

      p.x += p.vx;
      p.y += p.vy;

      if (p.x < 0 || p.x > this.width) p.vx *= -1;
      if (p.y < 0 || p.y > this.height) p.vy *= -1;

      // Mouse interaction
      if (this.mouse.x !== null && this.mouse.y !== null) {
        const dx = this.mouse.x - p.x;
        const dy = this.mouse.y - p.y;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < this.mouse.radius) {
          const force = (this.mouse.radius - dist) / this.mouse.radius;
          p.x -= (dx / dist) * force * 2.5;
          p.y -= (dy / dist) * force * 2.5;
        }
      }

      this.ctx.beginPath();
      this.ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
      this.ctx.fillStyle = p.color;
      this.ctx.shadowBlur = 8;
      this.ctx.shadowColor = p.color;
      this.ctx.fill();

      // Connect lines
      for (let j = i + 1; j < this.particles.length; j++) {
        const p2 = this.particles[j];
        const dx = p.x - p2.x;
        const dy = p.y - p2.y;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < this.maxDistance) {
          const opacity = (1 - dist / this.maxDistance) * 0.35;
          this.ctx.beginPath();
          this.ctx.moveTo(p.x, p.y);
          this.ctx.lineTo(p2.x, p2.y);
          this.ctx.strokeStyle = `rgba(0, 243, 255, ${opacity})`;
          this.ctx.lineWidth = 0.75;
          this.ctx.stroke();
        }
      }
    }
  }

  drawMatrix() {
    this.ctx.fillStyle = 'rgba(5, 7, 13, 0.08)';
    this.ctx.fillRect(0, 0, this.width, this.height);

    this.ctx.fillStyle = '#00ff66';
    this.ctx.font = `${this.fontSize}px 'JetBrains Mono', monospace`;

    const characters = '01TKJLINUXROUTERIPDNSHTTPSSHVLAN255PACKET010101';

    for (let i = 0; i < this.matrixColumns.length; i++) {
      const char = characters.charAt(Math.floor(Math.random() * characters.length));
      const x = i * this.fontSize;
      const y = this.matrixColumns[i] * this.fontSize;

      this.ctx.fillText(char, x, y);

      if (y > this.height && Math.random() > 0.975) {
        this.matrixColumns[i] = 0;
      }
      this.matrixColumns[i]++;
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.cyberNetwork = new CyberParticleNetwork('cyber-canvas');
});

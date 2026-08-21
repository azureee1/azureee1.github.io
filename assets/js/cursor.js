/* ==========================================================================
   FIQIH SANTOSO — CYBER TECH PORTFOLIO
   Cyber Cursor & 3D Interactive Card Physics
   ========================================================================== */

class CyberCursorAndPhysics {
  constructor() {
    this.dot = document.querySelector('.cyber-cursor-dot');
    this.ring = document.querySelector('.cyber-cursor-ring');
    this.cards = document.querySelectorAll('.cyber-box, .hologram-container');
    this.interactiveElements = document.querySelectorAll('a, button, input, textarea, .chip-btn, .filter-btn, .skill-card');

    this.mouse = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
    this.ringPos = { x: window.innerWidth / 2, y: window.innerHeight / 2 };

    this.init();
  }

  init() {
    if (window.innerWidth <= 768) return; // Disable custom cursor on mobile

    window.addEventListener('mousemove', (e) => {
      this.mouse.x = e.clientX;
      this.mouse.y = e.clientY;

      if (this.dot) {
        this.dot.style.transform = `translate(${this.mouse.x}px, ${this.mouse.y}px)`;
      }
    });

    this.renderRing();
    this.initHoverStates();
    this.init3DTilt();
  }

  renderRing() {
    // Smooth trailing ring physics
    this.ringPos.x += (this.mouse.x - this.ringPos.x) * 0.2;
    this.ringPos.y += (this.mouse.y - this.ringPos.y) * 0.2;

    if (this.ring) {
      this.ring.style.transform = `translate(${this.ringPos.x - 16}px, ${this.ringPos.y - 16}px)`;
    }

    requestAnimationFrame(() => this.renderRing());
  }

  initHoverStates() {
    this.interactiveElements.forEach(el => {
      el.addEventListener('mouseenter', () => {
        if (this.ring) {
          this.ring.style.width = '48px';
          this.ring.style.height = '48px';
          this.ring.style.borderColor = 'var(--neon-green)';
          this.ring.style.boxShadow = '0 0 20px rgba(0, 255, 102, 0.4)';
        }
        if (window.cyberSound) window.cyberSound.playHover();
      });

      el.addEventListener('mouseleave', () => {
        if (this.ring) {
          this.ring.style.width = '32px';
          this.ring.style.height = '32px';
          this.ring.style.borderColor = 'var(--neon-cyan)';
          this.ring.style.boxShadow = '0 0 15px rgba(0, 243, 255, 0.3)';
        }
      });

      el.addEventListener('click', () => {
        if (window.cyberSound) window.cyberSound.playClick();
      });
    });
  }

  init3DTilt() {
    const tiltContainers = document.querySelectorAll('.cyber-box, .hologram-container');

    tiltContainers.forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = ((y - centerY) / centerY) * -7;
        const rotateY = ((x - centerX) / centerX) * 7;

        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`;
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)';
      });
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.cyberCursor = new CyberCursorAndPhysics();
});

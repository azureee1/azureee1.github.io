/* ==========================================================================
   FIQIH SANTOSO — CYBER TECH PORTFOLIO
   Main Application Logic
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
  // 1. TYPING ANIMATION IN HERO
  initHeroTyping();

  // 2. NAVBAR & SMOOTH SCROLL
  initNavbar();

  // 3. SCROLL REVEAL OBSERVER
  initScrollReveal();

  // 4. SKILL BARS & FILTERING
  initSkills();

  // 5. PROJECT FILTERING & MODAL
  initProjects();

  // 6. CONTACT FORM & CLIPBOARD COPY
  initContact();

  // 7. BACK TO TOP & SCROLL PROGRESS
  initScrollProgress();

  // 8. AUDIO & MODE TOGGLES
  initToggles();
});

/* --------------------------------------------------------------------------
   1. Hero Dynamic Typing Effect
   -------------------------------------------------------------------------- */
function initHeroTyping() {
  const typingElem = document.getElementById('hero-typing-text');
  if (!typingElem) return;

  const phrases = [
    '> TKJ Student & Tech Explorer',
    '> Computer Networking Enthusiast',
    '> Linux & Ubuntu Server Administrator',
    '> Mikrotik RouterOS Specialist',
    '> Modern Web Developer',
    '> Future Cybersecurity Professional'
  ];

  let phraseIdx = 0;
  let charIdx = 0;
  let isDeleting = false;
  let typingSpeed = 80;

  function typeLoop() {
    const currentPhrase = phrases[phraseIdx];

    if (isDeleting) {
      typingElem.textContent = currentPhrase.substring(0, charIdx - 1);
      charIdx--;
      typingSpeed = 40;
    } else {
      typingElem.textContent = currentPhrase.substring(0, charIdx + 1);
      charIdx++;
      typingSpeed = 80;
    }

    if (!isDeleting && charIdx === currentPhrase.length) {
      isDeleting = true;
      typingSpeed = 1800; // Pause at end of phrase
    } else if (isDeleting && charIdx === 0) {
      isDeleting = false;
      phraseIdx = (phraseIdx + 1) % phrases.length;
      typingSpeed = 400; // Pause before typing new phrase
    }

    setTimeout(typeLoop, typingSpeed);
  }

  typeLoop();
}

/* --------------------------------------------------------------------------
   2. Navbar & Mobile Menu
   -------------------------------------------------------------------------- */
function initNavbar() {
  const navbar = document.querySelector('.cyber-navbar');
  const hamburger = document.querySelector('.nav-hamburger');
  const navLinks = document.querySelector('.nav-links');
  const links = document.querySelectorAll('.nav-link');

  // Scroll detection for navbar blur
  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });

  // Mobile menu toggle
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navLinks.classList.toggle('active');
      if (window.cyberSound) window.cyberSound.playClick();
    });

    links.forEach(link => {
      link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
      });
    });
  }

  // Active section tracking
  const sections = document.querySelectorAll('section[id]');
  window.addEventListener('scroll', () => {
    const scrollY = window.pageYOffset;
    sections.forEach(current => {
      const sectionHeight = current.offsetHeight;
      const sectionTop = current.offsetTop - 120;
      const sectionId = current.getAttribute('id');
      const targetLink = document.querySelector(`.nav-link[href*="${sectionId}"]`);

      if (targetLink && scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
        links.forEach(l => l.classList.remove('active'));
        targetLink.classList.add('active');
      }
    });
  });
}

/* --------------------------------------------------------------------------
   3. Scroll Reveal Observer
   -------------------------------------------------------------------------- */
function initScrollReveal() {
  const reveals = document.querySelectorAll('.reveal-init, .reveal-left, .reveal-right');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });

  reveals.forEach(el => observer.observe(el));
}

/* --------------------------------------------------------------------------
   4. Skills Matrix & Progress Bars
   -------------------------------------------------------------------------- */
function initSkills() {
  const skillCards = document.querySelectorAll('.skill-card');
  const filterBtns = document.querySelectorAll('.skills-filter-row .filter-btn');

  // Animate skill progress bars on scroll
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const fill = entry.target.querySelector('.skill-progress-fill');
        if (fill) {
          const percent = fill.getAttribute('data-progress') || '85%';
          fill.style.width = percent;
        }
      }
    });
  }, { threshold: 0.2 });

  skillCards.forEach(card => observer.observe(card));

  // Category Filtering
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.getAttribute('data-filter');

      skillCards.forEach(card => {
        const category = card.getAttribute('data-category');
        if (filter === 'all' || category === filter) {
          card.style.display = 'flex';
          setTimeout(() => { card.style.opacity = '1'; card.style.transform = 'scale(1)'; }, 20);
        } else {
          card.style.opacity = '0';
          card.style.transform = 'scale(0.95)';
          setTimeout(() => { card.style.display = 'none'; }, 200);
        }
      });
      if (window.cyberSound) window.cyberSound.playClick();
    });
  });
}

/* --------------------------------------------------------------------------
   5. Projects Showcase & Detail Modal
   -------------------------------------------------------------------------- */
const projectDetailsData = {
  'ubuntu-server': {
    title: 'Ubuntu Server Infrastructure & Web Hosting',
    category: 'LINUX / SERVER / CLOUD',
    desc: 'Implementasi dan konfigurasi server Linux Ubuntu 24.04 LTS skala produksi untuk keperluan web server (Nginx/Apache), MySQL database, FTP server, SSH secure remote access, integrasi SSL/TLS certificate, dan scheduled backup script via Cron.',
    tags: ['Ubuntu Server 24.04', 'Nginx', 'MySQL', 'SSH Hardening', 'UFW Firewall', 'Bash Script'],
    specs: [
      { label: 'OS & Kernel', value: 'Ubuntu Server 24.04 LTS (Linux 6.8)' },
      { label: 'Web Engine', value: 'Nginx High-Performance Reverse Proxy' },
      { label: 'Security Layer', value: 'UFW Firewall + Fail2ban + SSH Key Auth' },
      { label: 'Automation', value: 'Automated Daily DB Backup via Cron' }
    ],
    terminalCommand: '$ sudo apt update && sudo ufw enable && sudo systemctl status nginx'
  },
  'cisco-topology': {
    title: 'Enterprise Network Topology Simulation',
    category: 'NETWORKING / ROUTING / VLAN',
    desc: 'Perancangan topologi jaringan skala sekolah dan perusahaan menggunakan Cisco Packet Tracer. Meliputi konfigurasi Multi-VLAN antar departemen, Inter-VLAN Routing pada multilayer switch, OSPF dynamic routing protocol, DHCP Server Relay, dan Access Control List (ACL).',
    tags: ['Cisco Packet Tracer', 'VLAN', 'Inter-VLAN Routing', 'OSPF', 'DHCP Relay', 'ACL Security'],
    specs: [
      { label: 'Simulated Nodes', value: '6 Switches, 3 Routers, 30+ Client Workstations' },
      { label: 'Routing Protocol', value: 'OSPF Multi-Area Dynamic Routing' },
      { label: 'VLAN Segments', value: 'Admin (VLAN 10), Lab TKJ (VLAN 20), Guest (VLAN 30)' },
      { label: 'Security Policy', value: 'Standard & Extended ACL Traffic Filtering' }
    ],
    terminalCommand: '$ Router(config)# router ospf 1 -> network 192.168.10.0 0.0.0.255 area 0'
  },
  'mikrotik-hotspot': {
    title: 'Mikrotik RouterOS Bandwidth & Hotspot Gateway',
    category: 'NETWORKING / MIKROTIK / QOS',
    desc: 'Konfigurasi Mikrotik RouterBoard untuk pengelolaan jaringan internet gateway. Menggunakan Queue Tree & Simple Queue untuk pembagian bandwidth adil (QoS), User Manager untuk voucher hotspot siswa/guru, firewall filter rules untuk blokir situs terlarang, serta NAT masquerade.',
    tags: ['Mikrotik RouterOS', 'Winbox', 'Queue Tree QoS', 'Hotspot User Manager', 'NAT & Firewall', 'DNS Cache'],
    specs: [
      { label: 'Hardware Base', value: 'Mikrotik RB750Gr3 / RouterOS v7' },
      { label: 'Bandwidth QoS', value: 'PCQ (Per Connection Queue) Dynamic Equalizer' },
      { label: 'Auth System', value: 'Hotspot Captive Portal with Radius/User Manager' },
      { label: 'Security Rules', value: 'Drop Invalid Packets & Layer 7 Content Filter' }
    ],
    terminalCommand: '$ /ip hotspot add interface=wlan1 profile=hsprof1 name=TKJ_HOTSPOT'
  },
  'traffic-analysis': {
    title: 'Network Traffic & Wireshark Packet Analysis Lab',
    category: 'CYBERSECURITY / ANALYSIS',
    desc: 'Praktikum mendalam mengenai analisis protokol jaringan menggunakan Wireshark. Menganalisis 3-way TCP Handshake, DNS query resolution, deteksi ARP Spoofing/Poisoning, identifikasi bottleneck trafik jaringan LAN, dan audit port terbuka dengan Nmap.',
    tags: ['Wireshark', 'Nmap', 'TCP/IP Analysis', 'ARP Inspection', 'Protocol Audit', 'Security Lab'],
    specs: [
      { label: 'Analysis Tools', value: 'Wireshark Protocol Analyzer & Nmap CLI' },
      { label: 'Protocol Audited', value: 'TCP, UDP, ICMP, DNS, HTTP/HTTPS, ARP' },
      { label: 'Anomaly Detected', value: 'Broadcast Storms, Duplicate IPs, Port Sweeps' },
      { label: 'Output Report', value: 'Comprehensive Packet Health & Diagnostic Dossier' }
    ],
    terminalCommand: '$ tshark -i eth0 -f "tcp port 80" -w http_traffic.pcap'
  },
  'server-automation': {
    title: 'Linux Server Automation & Health Monitor Script',
    category: 'AUTOMATION / BASH / DEVOPS',
    desc: 'Pembuatan skrip bash otomatisasi untuk monitoring kesehatan server Linux secara berkala. Skrip mengecek penggunaan CPU, RAM, kapasitas storage disk, status service aktif, dan mengirimkan notifikasi peringatan jika utilisasi melebihi batas toleransi.',
    tags: ['Bash Scripting', 'Cron Schedule', 'System Monitoring', 'Log Rotation', 'Linux CLI'],
    specs: [
      { label: 'Script Language', value: 'POSIX Compliant Bash Shell Script' },
      { label: 'Monitoring Metrics', value: 'CPU Load, RAM Usage, Disk Space, Active Daemons' },
      { label: 'Alert Dispatch', value: 'Syslog Event Dispatch & Automated Log Archival' },
      { label: 'Trigger Mode', value: 'Every 5 minutes via System Crontab' }
    ],
    terminalCommand: '$ ./server_monitor.sh --status --check-all --verbose'
  },
  'fiqih-portfolio': {
    title: 'Fiqih Santoso — Cyber Tech Portfolio Web',
    category: 'WEB DEVELOPMENT / UI/UX',
    desc: 'Website portofolio interaktif bertema Cyberpunk Hacker & Futuristic Technology. Dibuat dengan arsitektur modern tanpa dependensi berat, menghadirkan Interactive Linux Terminal CLI, Live Canvas Telemetry Graph, Custom Cyber Cursor, 3D Tilt Card Physics, dan Web Audio Synthesizer.',
    tags: ['HTML5', 'Vanilla CSS3', 'JavaScript ES6+', 'Web Audio API', 'HTML5 Canvas', 'Responsive UI'],
    specs: [
      { label: 'Visual Theme', value: 'Cyberpunk Neon / Glassmorphism / HUD Interfaces' },
      { label: 'Interactive Features', value: 'Interactive Terminal, Canvas Graph, Audio Synthesizer' },
      { label: 'Performance', value: '100% Lightweight, Fast Load, Zero Bloat' },
      { label: 'Accessibility', value: 'Full Mobile Responsive & Prefers-Reduced-Motion' }
    ],
    terminalCommand: '$ git clone https://github.com/fiqihsantoso/cyber-portfolio.git'
  }
};

function initProjects() {
  const projectCards = document.querySelectorAll('.project-card');
  const filterBtns = document.querySelectorAll('.projects-filter-row .filter-btn');
  const modalOverlay = document.getElementById('project-modal');
  const modalCloseBtn = document.getElementById('modal-close-btn');

  // Category Filtering
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.getAttribute('data-filter');

      projectCards.forEach(card => {
        const category = card.getAttribute('data-category');
        if (filter === 'all' || category === filter) {
          card.style.display = 'flex';
          setTimeout(() => { card.style.opacity = '1'; card.style.transform = 'scale(1)'; }, 20);
        } else {
          card.style.opacity = '0';
          card.style.transform = 'scale(0.95)';
          setTimeout(() => { card.style.display = 'none'; }, 200);
        }
      });
      if (window.cyberSound) window.cyberSound.playClick();
    });
  });

  // Modal Open Trigger
  const viewBtns = document.querySelectorAll('.btn-view-project');
  viewBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const projId = btn.getAttribute('data-project');
      const data = projectDetailsData[projId];
      if (data && modalOverlay) {
        document.getElementById('modal-project-title').innerText = data.title;
        document.getElementById('modal-project-category').innerText = data.category;
        document.getElementById('modal-project-desc').innerText = data.desc;
        document.getElementById('modal-project-command').innerText = data.terminalCommand;

        // Tags
        const tagsContainer = document.getElementById('modal-project-tags');
        tagsContainer.innerHTML = '';
        data.tags.forEach(tag => {
          const span = document.createElement('span');
          span.className = 'badge-cyber';
          span.innerText = tag;
          tagsContainer.appendChild(span);
        });

        // Specs
        const specsContainer = document.getElementById('modal-project-specs');
        specsContainer.innerHTML = '';
        data.specs.forEach(spec => {
          const row = document.createElement('div');
          row.className = 'spec-row';
          row.innerHTML = `<span class="spec-label">${spec.label}</span><span class="spec-value">${spec.value}</span>`;
          specsContainer.appendChild(row);
        });

        modalOverlay.classList.add('active');
        if (window.cyberSound) window.cyberSound.playSuccess();
      }
    });
  });

  // Modal Close
  if (modalCloseBtn && modalOverlay) {
    modalCloseBtn.addEventListener('click', () => {
      modalOverlay.classList.remove('active');
      if (window.cyberSound) window.cyberSound.playClick();
    });

    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) {
        modalOverlay.classList.remove('active');
      }
    });
  }
}

/* --------------------------------------------------------------------------
   6. Contact Form & Clipboard Copy
   -------------------------------------------------------------------------- */
function initContact() {
  const form = document.getElementById('cyber-contact-form');
  const toast = document.getElementById('cyber-toast');
  const toastMsg = document.getElementById('toast-message');

  function showToast(message, isSuccess = true) {
    if (!toast || !toastMsg) return;
    toastMsg.innerText = message;
    toast.style.borderColor = isSuccess ? 'var(--neon-green)' : 'var(--neon-red)';
    toast.style.color = isSuccess ? 'var(--neon-green)' : 'var(--neon-red)';
    toast.classList.add('show');
    if (window.cyberSound) window.cyberSound.playSuccess();

    setTimeout(() => {
      toast.classList.remove('show');
    }, 4000);
  }

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;

      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span>ENCRYPTING & TRANSMITTING...</span>`;
      if (window.cyberSound) window.cyberSound.playClick();

      setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        form.reset();
        showToast('PACKET TRANSMITTED SUCCESSFULLY! [STATUS: 200 OK - FIQIH_NODE ACK]');
      }, 1500);
    });
  }

  // Copy to clipboard buttons
  const copyButtons = document.querySelectorAll('.btn-copy-data');
  copyButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const textToCopy = btn.getAttribute('data-copy');
      if (textToCopy) {
        navigator.clipboard.writeText(textToCopy).then(() => {
          showToast(`COPIED TO CLIPBOARD: "${textToCopy}"`);
        });
      }
    });
  });
}

/* --------------------------------------------------------------------------
   7. Back to Top & Scroll Progress
   -------------------------------------------------------------------------- */
function initScrollProgress() {
  const progressBar = document.querySelector('.scroll-progress-bar');
  const backToTopBtn = document.querySelector('.btn-back-top');

  window.addEventListener('scroll', () => {
    const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollProgress = totalHeight > 0 ? (window.pageYOffset / totalHeight) * 100 : 0;

    if (progressBar) {
      progressBar.style.width = `${scrollProgress}%`;
    }

    if (backToTopBtn) {
      if (window.pageYOffset > 400) {
        backToTopBtn.classList.add('visible');
      } else {
        backToTopBtn.classList.remove('visible');
      }
    }
  });

  if (backToTopBtn) {
    backToTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      if (window.cyberSound) window.cyberSound.playClick();
    });
  }
}

/* --------------------------------------------------------------------------
   8. Audio & Visual Mode Toggles
   -------------------------------------------------------------------------- */
function initToggles() {
  const audioBtn = document.getElementById('toggle-audio-btn');
  const modeBtn = document.getElementById('toggle-mode-btn');

  if (audioBtn) {
    audioBtn.addEventListener('click', () => {
      if (window.cyberSound) {
        const isMuted = window.cyberSound.toggleMute();
        audioBtn.innerHTML = isMuted 
          ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>`
          : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>`;
      }
    });
  }

  if (modeBtn) {
    modeBtn.addEventListener('click', () => {
      if (window.cyberNetwork) {
        const next = window.cyberNetwork.mode === 'network' ? 'matrix' : 'network';
        window.cyberNetwork.setMode(next);
        modeBtn.setAttribute('title', `Mode: ${next.toUpperCase()}`);
        if (window.cyberSound) window.cyberSound.playClick();
      }
    });
  }
}

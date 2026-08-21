/* ==========================================================================
   FIQIH SANTOSO — CYBER TECH PORTFOLIO
   Interactive Linux Terminal Emulator (FIQIH_OS v2.6)
   ========================================================================== */

class CyberTerminal {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    if (!this.container) return;

    this.body = this.container.querySelector('.terminal-body');
    this.output = this.container.querySelector('.terminal-output');
    this.input = this.container.querySelector('.terminal-input');
    this.chips = this.container.querySelectorAll('.chip-btn');

    this.history = [];
    this.historyIndex = -1;
    this.booted = false;

    this.commands = {
      help: () => this.cmdHelp(),
      about: () => this.cmdAbout(),
      skills: () => this.cmdSkills(),
      projects: () => this.cmdProjects(),
      services: () => this.cmdServices(),
      contact: () => this.cmdContact(),
      neofetch: () => this.cmdNeofetch(),
      system: () => this.cmdSystem(),
      ping: (args) => this.cmdPing(args),
      matrix: () => this.cmdMatrix(),
      network: () => this.cmdNetwork(),
      clear: () => this.cmdClear(),
      cls: () => this.cmdClear(),
      date: () => new Date().toString(),
      whoami: () => 'fiqih@tkj-node (Privilege: Student / Tech Enthusiast / Root)',
      sudo: () => 'Permission granted: You are authorized to explore all modules of Fiqih Santoso.',
      hack: () => this.cmdHack(),
      cat: (args) => this.cmdCat(args),
      uptime: () => 'Uptime: 248 days, 14 hours, 32 minutes | Load average: 0.14, 0.08, 0.05'
    };

    this.init();
  }

  init() {
    this.input.addEventListener('keydown', (e) => this.handleKeydown(e));

    this.chips.forEach(chip => {
      chip.addEventListener('click', () => {
        const cmd = chip.getAttribute('data-cmd') || chip.innerText.replace('$', '').trim();
        this.input.value = cmd;
        this.executeCommand(cmd);
        if (window.cyberSound) window.cyberSound.playClick();
      });
    });

    // Run boot sequence on first viewport entry
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !this.booted) {
          this.booted = true;
          this.runBootSequence();
          observer.unobserve(this.container);
        }
      });
    }, { threshold: 0.2 });

    observer.observe(this.container);
  }

  async runBootSequence() {
    const bootLogs = [
      '$ booting FIQIH_OS v2.6 [TKJ-CORE]...',
      '$ initializing hardware interfaces: eth0, wlan0... [OK]',
      '$ checking network connection: 192.168.1.100/24... ONLINE (18ms)',
      '$ loading Linux server modules (Nginx, SSH, Mikrotik-API)... [OK]',
      '$ loading skill matrix & project repository... [OK]',
      '$ security status: FIREWALL ACTIVE | INTEGRITY 100%',
      '$ Welcome to Fiqih Santoso Cyber Workspace! Type "help" for available commands.'
    ];

    this.output.innerHTML = '';
    for (const log of bootLogs) {
      await this.typeLine(log, 20);
      if (window.cyberSound) window.cyberSound.playKeyTick();
    }
  }

  typeLine(text, speed = 20) {
    return new Promise(resolve => {
      const line = document.createElement('div');
      line.className = 'term-log-line';
      if (text.includes('[OK]') || text.includes('ONLINE')) {
        line.innerHTML = text.replace(/\[OK\]/g, '<span style="color:var(--neon-green)">[OK]</span>')
                             .replace(/ONLINE/g, '<span style="color:var(--neon-cyan)">ONLINE</span>');
      } else if (text.startsWith('$')) {
        line.innerHTML = `<span style="color:var(--neon-cyan)">${text.substring(0, 1)}</span> ${text.substring(2)}`;
      } else {
        line.innerText = text;
      }
      this.output.appendChild(line);
      this.body.scrollTop = this.body.scrollHeight;
      setTimeout(resolve, speed);
    });
  }

  handleKeydown(e) {
    if (window.cyberSound) window.cyberSound.playKeyTick();

    if (e.key === 'Enter') {
      const val = this.input.value.trim();
      if (val) {
        this.history.push(val);
        this.historyIndex = this.history.length;
        this.executeCommand(val);
      }
      this.input.value = '';
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (this.historyIndex > 0) {
        this.historyIndex--;
        this.input.value = this.history[this.historyIndex];
      }
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        this.input.value = this.history[this.historyIndex];
      } else {
        this.historyIndex = this.history.length;
        this.input.value = '';
      }
    } else if (e.key === 'Tab') {
      e.preventDefault();
      const current = this.input.value.trim().toLowerCase();
      if (current) {
        const match = Object.keys(this.commands).find(c => c.startsWith(current));
        if (match) this.input.value = match;
      }
    }
  }

  executeCommand(rawCmd) {
    const parts = rawCmd.trim().split(' ');
    const cmd = parts[0].toLowerCase();
    const args = parts.slice(1);

    // Print command line
    const cmdLine = document.createElement('div');
    cmdLine.className = 'term-cmd-entry';
    cmdLine.innerHTML = `<span style="color:var(--neon-cyan)">fiqih@tkj-node:~$</span> <span style="color:#ffffff">${rawCmd}</span>`;
    this.output.appendChild(cmdLine);

    if (this.commands[cmd]) {
      const result = this.commands[cmd](args);
      if (result) {
        const resultLine = document.createElement('div');
        resultLine.className = 'term-result-entry';
        resultLine.innerHTML = result;
        this.output.appendChild(resultLine);
      }
    } else if (cmd !== '') {
      const errorLine = document.createElement('div');
      errorLine.className = 'term-error-entry';
      errorLine.innerHTML = `<span style="color:var(--neon-red)">Command not found: "${cmd}". Type "help" for a list of valid commands.</span>`;
      this.output.appendChild(errorLine);
      if (window.cyberSound) window.cyberSound.playBeep(220, 0.15, 'sawtooth');
    }

    this.body.scrollTop = this.body.scrollHeight;
  }

  cmdHelp() {
    return `
<span style="color:var(--neon-cyan)">AVAILABLE COMMANDS:</span>
  <span style="color:var(--neon-green)">help</span>       - Displays this list of commands
  <span style="color:var(--neon-green)">about</span>      - Summary of Fiqih Santoso & TKJ background
  <span style="color:var(--neon-green)">skills</span>     - List technical skills (Networking, Linux, Mikrotik)
  <span style="color:var(--neon-green)">projects</span>   - View featured projects & lab setups
  <span style="color:var(--neon-green)">services</span>   - View service offerings
  <span style="color:var(--neon-green)">contact</span>    - Show contact coordinates & channels
  <span style="color:var(--neon-green)">neofetch</span>   - Display system info & ASCII banner
  <span style="color:var(--neon-green)">system</span>     - Show server & security telemetry
  <span style="color:var(--neon-green)">ping</span>       - Test connection latency (e.g. ping 8.8.8.8)
  <span style="color:var(--neon-green)">matrix</span>     - Toggle cyberpunk matrix background mode
  <span style="color:var(--neon-green)">whoami</span>     - Current user session details
  <span style="color:var(--neon-green)">uptime</span>     - Display system uptime
  <span style="color:var(--neon-green)">clear</span>      - Clear terminal screen
`;
  }

  cmdAbout() {
    return `
<span style="color:var(--neon-cyan)">[ DOSSIER: FIQIH SANTOSO ]</span>
--------------------------------------------------
<span style="color:#ffffff">Name:</span>       Fiqih Santoso
<span style="color:#ffffff">Role:</span>       Student / TKJ / Network & Tech Enthusiast
<span style="color:#ffffff">Education:</span>  SMK Jurusan Teknik Komputer & Jaringan (TKJ)
<span style="color:#ffffff">Focus:</span>      Computer Networking, Ubuntu Server, Mikrotik RouterOS, Web Dev
<span style="color:#ffffff">Bio:</span>        Seorang siswa SMK TKJ yang antusias mendalami dunia infrastruktur jaringan, konfigurasi server Linux, manajemen router, dan pengembangan website modern.
`;
  }

  cmdSkills() {
    return `
<span style="color:var(--neon-cyan)">[ TECHNICAL SKILLS MATRIX ]</span>
--------------------------------------------------
* <span style="color:var(--neon-green)">Computer Networking</span> : TCP/IP, Subnetting, VLAN, Routing, Switching (92%)
* <span style="color:var(--neon-green)">Linux / Ubuntu</span>     : Ubuntu Server, Debian, CLI, Shell Scripting (88%)
* <span style="color:var(--neon-green)">Mikrotik RouterOS</span>  : Winbox, Bandwidth Queues, Hotspot, Firewall (86%)
* <span style="color:var(--neon-green)">Web Development</span>    : HTML5, CSS3, JavaScript ES6+, Responsive UI (84%)
* <span style="color:var(--neon-green)">Server Admin</span>       : Nginx, Apache, SSH, DNS, DHCP, Backups (85%)
* <span style="color:var(--neon-green)">Cybersecurity</span>      : Port Scanning, Firewall Hardening, Wireshark (78%)
`;
  }

  cmdProjects() {
    return `
<span style="color:var(--neon-cyan)">[ FEATURED PROJECT REPOSITORY ]</span>
--------------------------------------------------
1. <span style="color:#ffffff">Ubuntu Server Infrastructure</span> - LEMP Web Hosting, SSL & SSH Hardening
2. <span style="color:#ffffff">Enterprise Network Topology</span> - Cisco Packet Tracer Multi-VLAN & OSPF
3. <span style="color:#ffffff">Mikrotik Bandwidth & Hotspot</span> - RouterOS QoS Queues & Voucher System
4. <span style="color:#ffffff">Network Traffic Analysis</span>   - Wireshark Packet Inspection & Anomaly Scan
5. <span style="color:#ffffff">Fiqih Cyber Portfolio</span>      - Cyberpunk Interactive Tech Portfolio Web
`;
  }

  cmdServices() {
    return `
<span style="color:var(--neon-cyan)">[ SERVICES DEPLOYMENT ]</span>
--------------------------------------------------
* Network Administration & Topology Design
* Linux Server Setup (Ubuntu / Debian) & Maintenance
* Modern Web Development & Interactive Portfolios
* Hardware & Network Troubleshooting
* Cybersecurity & Network Hardening
`;
  }

  cmdContact() {
    return `
<span style="color:var(--neon-cyan)">[ COMMUNICATION CHANNELS ]</span>
--------------------------------------------------
* <span style="color:#ffffff">Email:</span>     fiqih.santoso@example.com
* <span style="color:#ffffff">Status:</span>    Available for Projects & Tech Collaborations
* <span style="color:#ffffff">Location:</span>  Indonesia
* Scroll to the Contact section below to transmit an encrypted payload!
`;
  }

  cmdNeofetch() {
    return `
<pre style="color:var(--neon-cyan); font-family:var(--font-mono); line-height:1.25;">
   _____ _       _ _        <span style="color:var(--neon-green)">fiqih@tkj-node</span>
  |  ___(_) __ _(_) |__     ----------------
  | |_  | |/ _\` | | '_ \\    <span style="color:#ffffff">OS:</span> FIQIH_OS Linux x86_64
  |  _| | | (_| | | | | |   <span style="color:#ffffff">Host:</span> TKJ Workstation v2.6
  |_|   |_|\\__, |_|_| |_|   <span style="color:#ffffff">Kernel:</span> 6.8.0-tkj-generic
              |_|           <span style="color:#ffffff">Uptime:</span> 248d 14h 32m
                            <span style="color:#ffffff">Shell:</span> bash 5.2.21
                            <span style="color:#ffffff">Role:</span> TKJ Student / Network Admin
                            <span style="color:#ffffff">Specialty:</span> Networking, Linux, Mikrotik
                            <span style="color:#ffffff">Theme:</span> Cyberpunk Neon [Active]
</pre>`;
  }

  cmdSystem() {
    return `
<span style="color:var(--neon-cyan)">[ SYSTEM TELEMETRY ]</span>
--------------------------------------------------
System Status    : <span style="color:var(--neon-green)">● ONLINE</span>
Network Status   : <span style="color:var(--neon-cyan)">CONNECTED (1.0 Gbps Fiber)</span>
Server Status    : <span style="color:var(--neon-green)">ACTIVE (Ubuntu 24.04 LTS)</span>
Firewall Status  : <span style="color:var(--neon-green)">PROTECTED (UFW / NAT Enabled)</span>
Active Ports     : 22(SSH), 80(HTTP), 443(HTTPS), 8291(Winbox)
`;
  }

  cmdPing(args) {
    const target = args[0] || '8.8.8.8';
    return `
PING ${target} (56 data bytes)
64 bytes from ${target}: icmp_seq=1 ttl=118 time=14.2 ms
64 bytes from ${target}: icmp_seq=2 ttl=118 time=15.8 ms
64 bytes from ${target}: icmp_seq=3 ttl=118 time=13.9 ms
--- ${target} ping statistics ---
3 packets transmitted, 3 received, 0% packet loss, time 2004ms
rtt min/avg/max = 13.9/14.6/15.8 ms
`;
  }

  cmdMatrix() {
    if (window.cyberNetwork) {
      const nextMode = window.cyberNetwork.mode === 'network' ? 'matrix' : 'network';
      window.cyberNetwork.setMode(nextMode);
      return `<span style="color:var(--neon-green)">Switched visual canvas mode to: ${nextMode.toUpperCase()}</span>`;
    }
    return 'Canvas controller not loaded.';
  }

  cmdNetwork() {
    return `
<span style="color:var(--neon-cyan)">[ NETWORK INTERFACE CONFIGURATION ]</span>
eth0: flags=4163<UP,BROADCAST,RUNNING,MULTICAST> mtu 1500
      inet 192.168.1.100  netmask 255.255.255.0  broadcast 192.168.1.255
      inet6 fe80::a00:27ff:fe4e:66a1  prefixlen 64  scopeid 0x20<link>
      RX packets 1489201  bytes 129480192 (129.4 MB)
      TX packets 982014   bytes 89201948 (89.2 MB)
`;
  }

  cmdHack() {
    return `
<span style="color:var(--neon-pink)">[ HACK SIMULATION INITIATED ]</span>
Bypassing proxy server... [OK]
Injecting cybersecurity payload... [OK]
Access level elevated to: <span style="color:var(--neon-green)">ROOT ADMINISTRATOR</span>
"The quieter you become, the more you are able to hear."
`;
  }

  cmdCat(args) {
    const file = args[0] || '';
    if (file === 'about.txt' || file === 'about') return this.cmdAbout();
    if (file === 'skills.txt' || file === 'skills') return this.cmdSkills();
    return `cat: ${file || 'null'}: No such file or directory. Try "cat about" or "cat skills"`;
  }

  cmdClear() {
    this.output.innerHTML = '';
    return '';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.cyberTerminal = new CyberTerminal('interactive-terminal');
});

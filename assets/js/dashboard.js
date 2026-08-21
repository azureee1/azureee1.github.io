/* ==========================================================================
   FIQIH SANTOSO — CYBER TECH PORTFOLIO
   Live Telemetry & Cybersecurity Dashboard
   ========================================================================== */

class CyberDashboard {
  constructor() {
    this.chartCanvas = document.getElementById('traffic-chart');
    this.chartCtx = this.chartCanvas ? this.chartCanvas.getContext('2d') : null;
    this.trafficData = [];
    this.maxPoints = 25;

    this.uptimeSeconds = 248 * 86400 + 14 * 3600 + 32 * 60 + 19;
    this.init();
  }

  init() {
    if (this.chartCanvas) {
      this.resizeChart();
      window.addEventListener('resize', () => this.resizeChart());
      this.initTrafficData();
      this.startTrafficLoop();
    }

    this.startUptimeClock();
    this.startHardwareSimulation();
  }

  resizeChart() {
    const parent = this.chartCanvas.parentElement;
    if (parent) {
      this.chartCanvas.width = parent.clientWidth;
      this.chartCanvas.height = parent.clientHeight;
    }
  }

  initTrafficData() {
    this.trafficData = [];
    for (let i = 0; i < this.maxPoints; i++) {
      this.trafficData.push({
        tx: 40 + Math.random() * 50,
        rx: 60 + Math.random() * 60
      });
    }
  }

  startTrafficLoop() {
    setInterval(() => {
      // Add new simulated packet throughput
      const lastTx = this.trafficData[this.trafficData.length - 1].tx;
      const lastRx = this.trafficData[this.trafficData.length - 1].rx;

      const newTx = Math.min(100, Math.max(15, lastTx + (Math.random() - 0.5) * 35));
      const newRx = Math.min(100, Math.max(25, lastRx + (Math.random() - 0.5) * 45));

      this.trafficData.shift();
      this.trafficData.push({ tx: newTx, rx: newRx });

      // Update text indicators if present
      const txElem = document.getElementById('live-tx-rate');
      const rxElem = document.getElementById('live-rx-rate');
      if (txElem) txElem.innerText = `${(newTx * 12.4).toFixed(1)} KB/s`;
      if (rxElem) rxElem.innerText = `${(newRx * 18.2).toFixed(1)} KB/s`;

      this.drawChart();
    }, 900);
  }

  drawChart() {
    if (!this.chartCtx) return;
    const ctx = this.chartCtx;
    const w = this.chartCanvas.width;
    const h = this.chartCanvas.height;

    ctx.clearRect(0, 0, w, h);

    // Draw Grid Lines
    ctx.strokeStyle = 'rgba(0, 243, 255, 0.08)';
    ctx.lineWidth = 1;
    for (let y = 0; y < h; y += 30) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(w, y);
      ctx.stroke();
    }

    const stepX = w / (this.maxPoints - 1);

    // Draw RX Line (Cyan)
    ctx.beginPath();
    ctx.strokeStyle = '#00f3ff';
    ctx.lineWidth = 2;
    ctx.shadowBlur = 10;
    ctx.shadowColor = '#00f3ff';

    for (let i = 0; i < this.trafficData.length; i++) {
      const x = i * stepX;
      const y = h - (this.trafficData[i].rx / 100) * (h - 20) - 10;
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
    ctx.stroke();

    // Draw TX Line (Green)
    ctx.beginPath();
    ctx.strokeStyle = '#00ff66';
    ctx.lineWidth = 1.5;
    ctx.shadowBlur = 10;
    ctx.shadowColor = '#00ff66';

    for (let i = 0; i < this.trafficData.length; i++) {
      const x = i * stepX;
      const y = h - (this.trafficData[i].tx / 100) * (h - 20) - 10;
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    }
    ctx.stroke();
  }

  startUptimeClock() {
    const uptimeElem = document.getElementById('dash-uptime-value');
    setInterval(() => {
      this.uptimeSeconds++;
      const days = Math.floor(this.uptimeSeconds / 86400);
      const hours = Math.floor((this.uptimeSeconds % 86400) / 3600);
      const minutes = Math.floor((this.uptimeSeconds % 3600) / 60);
      const seconds = this.uptimeSeconds % 60;

      if (uptimeElem) {
        uptimeElem.innerText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
      }
    }, 1000);
  }

  startHardwareSimulation() {
    const cpuElem = document.getElementById('dash-cpu-val');
    const ramElem = document.getElementById('dash-ram-val');
    const pingElem = document.getElementById('navbar-ping-val');

    setInterval(() => {
      const cpu = Math.floor(18 + Math.random() * 15);
      const ram = (2.4 + (Math.random() - 0.5) * 0.2).toFixed(1);
      const ping = Math.floor(14 + Math.random() * 8);

      if (cpuElem) cpuElem.innerText = `${cpu}%`;
      if (ramElem) ramElem.innerText = `${ram} GB / 8 GB`;
      if (pingElem) pingElem.innerText = `${ping}ms`;
    }, 2500);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.cyberDashboard = new CyberDashboard();
});

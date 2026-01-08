class PatternLock {
  constructor(id) {
    this.lock = document.getElementById(id);
    this.canvas = this.lock.querySelector("canvas");
    this.ctx = this.canvas.getContext("2d");
    this.grid = this.lock.querySelector(".grid");
    this.display = this.lock.closest(".lock-wrapper").querySelector(".pattern");
    this.pattern = [];
    this.drawing = false;
    this.pos = { x: 0, y: 0 };
    this.dots = [];
    this.centers = [];
    this.init();
  }

  init() {
    for (let i = 1; i <= 9; i++) {
      const dot = document.createElement("div");
      dot.className = "dot";
      dot.dataset.num = i;
      dot.innerHTML = `<span class="num">${i}</span><div class="circle"></div>`;
      this.grid.appendChild(dot);
      this.dots.push(dot);
    }
    this.setupCanvas();
    this.calcCenters();
    this.events();
  }

  setupCanvas() {
    const rect = this.lock.getBoundingClientRect();
    this.canvas.width = rect.width;
    this.canvas.height = rect.height;
    this.calcCenters();
  }

  calcCenters() {
    const rect = this.lock.getBoundingClientRect();
    this.centers = [];
    this.dots.forEach((dot) => {
      const circle = dot.querySelector(".circle");
      const r = circle.getBoundingClientRect();
      this.centers.push({
        num: +dot.dataset.num,
        x: r.left + r.width / 2 - rect.left,
        y: r.top + r.height / 2 - rect.top,
      });
    });
  }

  events() {
    const getPos = (e) => {
      const rect = this.lock.getBoundingClientRect();
      const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
      const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
      return { x, y };
    };

    const start = (e) => {
      this.drawing = true;
      const pos = getPos(e);
      const num = this.findDot(pos.x, pos.y);
      if (num) this.add(num);
    };

    const move = (e) => {
      if (!this.drawing) return;
      e.preventDefault();
      this.pos = getPos(e);
      const num = this.findDot(this.pos.x, this.pos.y);
      if (num) this.add(num);
      this.draw();
    };

    const end = () => {
      this.drawing = false;
      this.draw();
    };

    this.lock.addEventListener("mousedown", start);
    this.lock.addEventListener("mousemove", move);
    document.addEventListener("mouseup", end);
    this.lock.addEventListener("touchstart", start, { passive: false });
    this.lock.addEventListener("touchmove", move, { passive: false });
    document.addEventListener("touchend", end);
  }

  findDot(x, y) {
    for (let d of this.centers) {
      const dist = Math.sqrt((x - d.x) ** 2 + (y - d.y) ** 2);
      if (dist < 35) return d.num;
    }
    return null;
  }

  add(num) {
    if (this.pattern.includes(num)) return;
    this.pattern.push(num);
    this.dots.find((d) => +d.dataset.num === num).classList.add("active");
    this.updateDisplay();
  }

  draw() {
    const w = this.canvas.width,
      h = this.canvas.height;
    this.ctx.clearRect(0, 0, w, h);
    if (this.pattern.length === 0) return;

    this.ctx.strokeStyle = "#000";
    this.ctx.lineWidth = 3;
    this.ctx.lineCap = "round";
    this.ctx.beginPath();

    const first = this.centers.find((d) => d.num === this.pattern[0]);
    this.ctx.moveTo(first.x, first.y);

    for (let i = 1; i < this.pattern.length; i++) {
      const d = this.centers.find((c) => c.num === this.pattern[i]);
      this.ctx.lineTo(d.x, d.y);
    }

    if (this.drawing) this.ctx.lineTo(this.pos.x, this.pos.y);
    this.ctx.stroke();

    this.pattern.forEach((num) => {
      const d = this.centers.find((c) => c.num === num);
      this.ctx.beginPath();
      this.ctx.arc(d.x, d.y, 5, 0, Math.PI * 2);
      this.ctx.fillStyle = "#000";
      this.ctx.fill();
    });
  }

  updateDisplay() {
    if (this.pattern.length > 0) {
      this.display.textContent = this.pattern.join(" - ");
      this.display.classList.remove("empty");
    } else {
      this.display.textContent = "- - -";
      this.display.classList.add("empty");
    }
  }

  getPattern() {
    return this.pattern.length >= 4
      ? {
          success: true,
          pattern: this.pattern.join(""),
          array: [...this.pattern],
        }
      : { success: false, message: "El patrón debe tener al menos 4 puntos" };
  }

  reset() {
    this.pattern = [];
    this.drawing = false;
    this.dots.forEach((d) => d.classList.remove("active"));
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    this.updateDisplay();
  }
}

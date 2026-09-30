document.addEventListener("DOMContentLoaded", () => {
    /* --- 1. Password Toggle Functionality --- */
    const passwordInput = document.getElementById("password");
    const toggleBtn = document.getElementById("togglePasswordBtn");
    const toggleIcon = document.getElementById("toggleIcon");

    if (passwordInput && toggleBtn && toggleIcon) {
        toggleBtn.addEventListener("click", (e) => {
            e.preventDefault();

            const isPassword = passwordInput.type === "password";

            // Toggle field input type
            passwordInput.type = isPassword ? "text" : "password";

            // Toggle Tabler Icons
            if (toggleIcon.classList.contains("ti")) {
                toggleIcon.classList.toggle("ti-eye", !isPassword);
                toggleIcon.classList.toggle("ti-eye-off", isPassword);
            }
            // Toggle Font Awesome Icons
            else if (
                toggleIcon.classList.contains("fa") ||
                toggleIcon.classList.contains("fas") ||
                toggleIcon.classList.contains("far") ||
                toggleIcon.classList.contains("fa-solid")
            ) {
                toggleIcon.classList.toggle("fa-eye", !isPassword);
                toggleIcon.classList.toggle("fa-eye-slash", isPassword);
            }
        });
    } else {
        console.warn("Password toggle elements were not found in the DOM.");
    }

    /* --- 2. Interactive Canvas Particle System --- */
    const canvas = document.getElementById("particleCanvas");

    // Guard clause: stop particle script if canvas does not exist
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    const particles = [];
    const particleCount = Math.floor((width * height) / 18000);

    window.addEventListener("resize", () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.radius = Math.random() * 1.5 + 0.5;
            this.vx = (Math.random() - 0.5) * 0.4;
            this.vy = (Math.random() - 0.5) * 0.4;
            this.alpha = Math.random() * 0.5 + 0.2;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;

            if (this.x < 0) this.x = width;
            if (this.x > width) this.x = 0;
            if (this.y < 0) this.y = height;
            if (this.y > height) this.y = 0;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(16, 185, 129, ${this.alpha})`;
            ctx.fill();
        }
    }

    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function connectParticles() {
        for (let a = 0; a < particles.length; a++) {
            for (let b = a + 1; b < particles.length; b++) {
                const dx = particles[a].x - particles[b].x;
                const dy = particles[a].y - particles[b].y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 110) {
                    const opacity = (1 - distance / 110) * 0.15;
                    ctx.strokeStyle = `rgba(16, 185, 129, ${opacity})`;
                    ctx.lineWidth = 0.8;
                    ctx.beginPath();
                    ctx.moveTo(particles[a].x, particles[a].y);
                    ctx.lineTo(particles[b].x, particles[b].y);
                    ctx.stroke();
                }
            }
        }
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);

        particles.forEach((particle) => {
            particle.update();
            particle.draw();
        });

        connectParticles();
        requestAnimationFrame(animate);
    }

    animate();
});

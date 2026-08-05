// main.js — small progressive-enhancement helpers, no framework

document.addEventListener('DOMContentLoaded', () => {
    // --- Click-to-copy server IP ---
    const copyBtn = document.querySelector('[data-copy-ip]');
    if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
            const ip = copyBtn.getAttribute('data-copy-ip');
            try {
                await navigator.clipboard.writeText(ip);
            } catch (err) {
                // Fallback for older browsers
                const tmp = document.createElement('textarea');
                tmp.value = ip;
                document.body.appendChild(tmp);
                tmp.select();
                document.execCommand('copy');
                document.body.removeChild(tmp);
            }
            const original = copyBtn.innerHTML;
            copyBtn.innerHTML = '✅ Copied!';
            copyBtn.classList.add('copied');
            setTimeout(() => {
                copyBtn.innerHTML = original;
                copyBtn.classList.remove('copied');
            }, 1800);
        });
    }

    // --- Mock live player count ticker (visual flourish only) ---
    const counter = document.querySelector('[data-player-count]');
    if (counter) {
        const base = parseInt(counter.getAttribute('data-player-count'), 10) || 0;
        let current = base;
        setInterval(() => {
            const delta = Math.floor(Math.random() * 5) - 2; // -2..+2
            current = Math.max(0, current + delta);
            counter.textContent = current.toLocaleString();
        }, 4000);
    }

    // --- Confirm destructive actions ---
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});

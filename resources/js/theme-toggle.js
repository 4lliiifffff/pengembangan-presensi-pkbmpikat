/**
 * Day / Night Theme Switcher Handler
 * Supports Uiverse.io Day/Night Toggle (.dn-input, #dn, #dnLogin, etc.)
 * Synchronizes state with data-theme on <html> and localStorage.
 * Implements Instant-Transition-Suppression to eliminate lag/jank during theme toggle.
 */

function syncThemeUI(theme) {
    const isDark = theme === 'dark';
    const dnInputs = document.querySelectorAll('.dn-input, #dn, #dnLogin, #dnError');
    dnInputs.forEach(input => {
        if (input.checked !== isDark) {
            input.checked = isDark;
        }
    });

    const icons = document.querySelectorAll('#themeToggleIcon, #themeIcon');
    icons.forEach(icon => {
        icon.setAttribute('name', isDark ? 'sunny-outline' : 'moon-outline');
    });
}

export function switchThemeInstantly(nextTheme) {
    const root = document.documentElement;

    // 1. Matikan transisi CSS sementara pada seluruh DOM tree agar pergantian tema 60 FPS instan tanpa lag
    const style = document.createElement('style');
    style.id = 'theme-transition-suppressor';
    style.appendChild(
        document.createTextNode(
            `*, *::before, *::after {
                -webkit-transition: none !important;
                -moz-transition: none !important;
                -o-transition: none !important;
                -ms-transition: none !important;
                transition: none !important;
            }
            .toggle, .toggle__handler, .star, .crater {
                transition: inherit !important;
            }`
        )
    );
    document.head.appendChild(style);

    // 2. Set tema baru di atribut HTML dan simpan ke localStorage
    root.setAttribute('data-theme', nextTheme);
    try {
        localStorage.setItem('theme', nextTheme);
    } catch (_) {}

    // 3. Sinkronkan seluruh toggle switch dan ikon di layar
    syncThemeUI(nextTheme);

    // 4. Force reflow 1 frame agar browser meng-apply warna baru seketika
    void root.offsetHeight;

    // 5. Kembalikan transisi normal di frame berikutnya untuk interaksi hover
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            const el = document.getElementById('theme-transition-suppressor');
            if (el && el.parentNode) {
                el.parentNode.removeChild(el);
            }
        });
    });
}

export function initThemeToggle() {
    const root = document.documentElement;
    const currentTheme = root.getAttribute('data-theme') || localStorage.getItem('theme') || 'light';
    syncThemeUI(currentTheme);

    const dnInputs = document.querySelectorAll('.dn-input, #dn, #dnLogin, #dnError');
    dnInputs.forEach(input => {
        if (input.dataset.themeBound) return;
        input.dataset.themeBound = 'true';

        input.addEventListener('change', () => {
            const next = input.checked ? 'dark' : 'light';
            switchThemeInstantly(next);
        });
    });

    const legacyBtns = document.querySelectorAll('#themeToggleBtn, .login-theme-toggle, .error-theme-btn');
    legacyBtns.forEach(btn => {
        if (btn.dataset.themeBound) return;
        if (btn.querySelector('.dn-input') || btn.classList.contains('toggleWrapper')) return;
        btn.dataset.themeBound = 'true';

        btn.addEventListener('click', (e) => {
            if (e.target.closest('.toggleWrapper')) return;
            const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            switchThemeInstantly(current);
        });
    });
}

// Auto-run on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeToggle);
} else {
    initThemeToggle();
}

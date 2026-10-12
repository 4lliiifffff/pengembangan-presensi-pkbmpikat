/**
 * Day / Night Theme Switcher Handler
 * Supports Uiverse.io Day/Night Toggle (.dn-input, #dn, #dnLogin, etc.)
 * Synchronizes state with data-theme on <html> and localStorage.
 */

export function initThemeToggle() {
    const root = document.documentElement;
    const dnInputs = document.querySelectorAll('.dn-input, #dn, #dnLogin, #dnError');
    const legacyBtns = document.querySelectorAll('#themeToggleBtn, .login-theme-toggle, .error-theme-btn');

    function syncThemeUI(theme) {
        const isDark = theme === 'dark';
        dnInputs.forEach(input => {
            input.checked = isDark;
        });

        const icons = document.querySelectorAll('#themeToggleIcon, #themeIcon');
        icons.forEach(icon => {
            icon.setAttribute('name', isDark ? 'sunny-outline' : 'moon-outline');
        });
    }

    const currentTheme = root.getAttribute('data-theme') || localStorage.getItem('theme') || 'light';
    syncThemeUI(currentTheme);

    dnInputs.forEach(input => {
        // Prevent duplicate listeners
        if (input.dataset.themeBound) return;
        input.dataset.themeBound = 'true';

        input.addEventListener('change', () => {
            const next = input.checked ? 'dark' : 'light';
            root.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            syncThemeUI(next);
        });
    });

    legacyBtns.forEach(btn => {
        if (btn.dataset.themeBound) return;
        if (btn.querySelector('.dn-input') || btn.classList.contains('toggleWrapper')) return;
        btn.dataset.themeBound = 'true';

        btn.addEventListener('click', (e) => {
            if (e.target.closest('.toggleWrapper')) return;
            const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', current);
            localStorage.setItem('theme', current);
            syncThemeUI(current);
        });
    });
}

// Auto-run on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeToggle);
} else {
    initThemeToggle();
}

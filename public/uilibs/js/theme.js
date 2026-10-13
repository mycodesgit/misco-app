document.addEventListener("DOMContentLoaded", function () {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const themeLabel = document.getElementById('themeLabel');
    const htmlElement = document.documentElement;

    function updateThemeUI(theme) {
        const isDark = theme === 'dark';

        // 1. Sync checkbox position
        if (themeToggle) {
            themeToggle.checked = isDark;
        }

        // 2. Update icon and label text based on current mode
        if (isDark) {
            if (themeIcon) themeIcon.className = 'ti ti-moon text-warning';
            if (themeLabel) themeLabel.textContent = 'Dark';
        } else {
            if (themeIcon) themeIcon.className = 'ti ti-sun';
            if (themeLabel) themeLabel.textContent = 'Light';
        }
    }

    // Read saved user choice only — default is light. No system lookup.
    var stored = null;
    try {
        stored = localStorage.getItem('theme');
    } catch (e) {
        stored = null;
    }
    const currentTheme = htmlElement.getAttribute('data-bs-theme') || stored || 'light';
    htmlElement.setAttribute('data-bs-theme', currentTheme);

    // Set initial UI state
    updateThemeUI(currentTheme);

    // Toggle theme on change
    if (themeToggle) {
        themeToggle.addEventListener('change', function () {
            const newTheme = this.checked ? 'dark' : 'light';

            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeUI(newTheme);
        });
    }
});

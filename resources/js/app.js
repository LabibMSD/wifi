const themeToggle = document.querySelector('[data-theme-toggle]');

if (themeToggle instanceof HTMLButtonElement) {
    const root = document.documentElement;
    const themePreferenceKey = 'eponet-theme';
    const systemThemePreference = window.matchMedia('(prefers-color-scheme: dark)');

    const updateTheme = (theme) => {
        const isDark = theme === 'dark';
        const label = isDark ? themeToggle.dataset.labelLight : themeToggle.dataset.labelDark;

        root.classList.toggle('dark', isDark);
        root.style.colorScheme = theme;
        themeToggle.setAttribute('aria-label', label);
        themeToggle.setAttribute('title', label);
    };

    const getSavedTheme = () => {
        try {
            const theme = window.localStorage.getItem(themePreferenceKey);

            return theme === 'light' || theme === 'dark' ? theme : null;
        } catch (error) {
            if (!(error instanceof DOMException) || error.name !== 'SecurityError') {
                throw error;
            }

            console.warn('Eponet could not access the saved theme preference.', error);

            return null;
        }
    };

    const saveTheme = (theme) => {
        try {
            window.localStorage.setItem(themePreferenceKey, theme);
        } catch (error) {
            if (!(error instanceof DOMException) || !['QuotaExceededError', 'SecurityError'].includes(error.name)) {
                throw error;
            }

            console.warn('Eponet could not save the theme preference.', error);
        }
    };

    updateTheme(root.classList.contains('dark') ? 'dark' : 'light');

    themeToggle.addEventListener('click', () => {
        const theme = root.classList.contains('dark') ? 'light' : 'dark';

        updateTheme(theme);
        saveTheme(theme);
    });

    systemThemePreference.addEventListener('change', (event) => {
        if (getSavedTheme() === null) {
            updateTheme(event.matches ? 'dark' : 'light');
        }
    });
}

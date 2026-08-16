/**
 * Shared admin theme loader.
 * Applies CMS design settings (theme, typography, branding) across admin pages.
 */
(function initAdminTheme() {
    const GOOGLE_FONT_BASE = 'https://fonts.googleapis.com/css2?family=';
    const LOTTIE_PLAYER_SRC = 'https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js';
    const LOTTIE_LOGO_JSON = '/src/wkc-logo.json';

    function slugifyFont(fontName) {
        return String(fontName || '').trim().replace(/\s+/g, '+');
    }

    function ensureFontLoaded(fontName) {
        const font = String(fontName || '').trim();
        if (!font) return;
        if (font.toLowerCase() === 'forte') return;
        if (document.querySelector(`link[data-admin-font="${font}"]`)) return;
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `${GOOGLE_FONT_BASE}${slugifyFont(font)}:wght@300;400;500;600;700;800;900&display=swap`;
        link.setAttribute('data-admin-font', font);
        document.head.appendChild(link);
    }

    function injectThemeCssOnce() {
        if (document.getElementById('admin-theme-runtime-css')) return;
        const style = document.createElement('style');
        style.id = 'admin-theme-runtime-css';
        style.textContent = `
            :root {
                --admin-primary: #7c3aed;
                --admin-secondary: #5b21b6;
                --admin-accent: #a78bfa;
                --admin-bg: #f5f8f7;
                --admin-surface: #ffffff;
                --admin-text: #111827;
                --admin-font-heading: "Public Sans", sans-serif;
                --admin-font-body: "Public Sans", sans-serif;
            }

            body { font-family: var(--admin-font-body); background-color: var(--admin-bg); color: var(--admin-text); }
            h1, h2, h3, h4, h5, h6 { font-family: var(--admin-font-heading); }

            .bg-primary { background-color: var(--admin-primary) !important; }
            .text-primary { color: var(--admin-primary) !important; }
            .border-primary { border-color: var(--admin-primary) !important; }
            .hover\\:bg-primary:hover { background-color: var(--admin-primary) !important; }
            .hover\\:text-primary:hover { color: var(--admin-primary) !important; }

            .bg-primary\\/10 { background-color: color-mix(in srgb, var(--admin-primary) 10%, transparent) !important; }
            .bg-primary\\/15 { background-color: color-mix(in srgb, var(--admin-primary) 15%, transparent) !important; }
            .bg-primary\\/20 { background-color: color-mix(in srgb, var(--admin-primary) 20%, transparent) !important; }

            .from-primary { --tw-gradient-from: var(--admin-primary) var(--tw-gradient-from-position) !important; }
            .to-primary-dark { --tw-gradient-to: var(--admin-secondary) var(--tw-gradient-to-position) !important; }

            .sidebar-link.active {
                background: color-mix(in srgb, var(--admin-primary) 12%, transparent) !important;
                color: var(--admin-primary) !important;
            }
        `;
        document.head.appendChild(style);
    }

    function applyTheme(theme) {
        if (!theme) return;
        const root = document.documentElement;
        if (theme.primary) root.style.setProperty('--admin-primary', theme.primary);
        if (theme.secondary) root.style.setProperty('--admin-secondary', theme.secondary);
        if (theme.accent) root.style.setProperty('--admin-accent', theme.accent);
        if (theme.background) root.style.setProperty('--admin-bg', theme.background);
        if (theme.surface) root.style.setProperty('--admin-surface', theme.surface);
        if (theme.text) root.style.setProperty('--admin-text', theme.text);
    }

    function applyTypography(typography) {
        if (!typography) return;
        const root = document.documentElement;
        if (typography.headingFont) {
            ensureFontLoaded(typography.headingFont);
            root.style.setProperty('--admin-font-heading', `"${typography.headingFont}", sans-serif`);
        }
        if (typography.bodyFont) {
            ensureFontLoaded(typography.bodyFont);
            root.style.setProperty('--admin-font-body', `"${typography.bodyFont}", sans-serif`);
        }
    }

    function ensureLottiePlayerLoaded() {
        if (window.lottie) return Promise.resolve(window.lottie);

        const existing = document.getElementById('wkc-admin-lottie-player');
        if (existing) {
            return new Promise((resolve, reject) => {
                existing.addEventListener('load', () => resolve(window.lottie), { once: true });
                existing.addEventListener('error', reject, { once: true });
            });
        }

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.id = 'wkc-admin-lottie-player';
            script.src = LOTTIE_PLAYER_SRC;
            script.async = true;
            script.onload = () => resolve(window.lottie);
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    function replaceLogoImageWithLottie(img) {
        if (!img || img.dataset.lottieApplied === '1') return;
        const width = img.clientWidth || img.naturalWidth || Math.max(parseInt(img.getAttribute('width') || '0', 10), 140);
        const height = img.clientHeight || img.naturalHeight || Math.max(parseInt(img.getAttribute('height') || '0', 10), 40);

        const lottieHost = document.createElement('span');
        lottieHost.className = 'inline-flex items-center';
        lottieHost.style.width = `${width}px`;
        lottieHost.style.height = `${height}px`;
        lottieHost.style.maxWidth = '100%';
        lottieHost.setAttribute('aria-label', img.getAttribute('alt') || 'Logo');
        lottieHost.setAttribute('role', 'img');

        img.style.display = 'none';
        img.dataset.lottieApplied = '1';
        img.insertAdjacentElement('afterend', lottieHost);

        if (!window.lottie) return;
        window.lottie.loadAnimation({
            container: lottieHost,
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: LOTTIE_LOGO_JSON,
            rendererSettings: {
                preserveAspectRatio: 'xMidYMid meet',
            },
        });
    }

    async function applyLottieAdminLogos() {
        const logos = Array.from(document.querySelectorAll('[data-lottie-logo], img[src*="wkc-logo"], [data-admin-brand-logo="header"]'));
        if (!logos.length) return;

        try {
            await ensureLottiePlayerLoaded();
            logos.forEach(replaceLogoImageWithLottie);
        } catch (err) {
            console.warn('Admin lottie logo could not be initialized:', err);
        }
    }

    function applyBranding(branding) {
        if (!branding) return;
        if (branding.siteName) {
            document.querySelectorAll('[data-admin-site-name]').forEach((el) => {
                el.textContent = branding.siteName;
            });
        }
        if (branding.favicon) {
            let favicon = document.querySelector('link[rel="icon"]');
            if (!favicon) {
                favicon = document.createElement('link');
                favicon.rel = 'icon';
                document.head.appendChild(favicon);
            }
            favicon.href = branding.favicon;
        }

        applyLottieAdminLogos();
    }

    async function loadAdminSettings() {
        injectThemeCssOnce();
        applyLottieAdminLogos();
        try {
            const res = await fetch('../api/settings.php?scope=admin', { credentials: 'include' });
            if (!res.ok) return;
            const data = await res.json();
            const settings = data.settings || {};
            applyTheme(settings.theme || {});
            applyTypography(settings.typography || {});
            applyBranding(settings.branding || {});
            window.CMS_ADMIN_SETTINGS = settings;
            document.dispatchEvent(new CustomEvent('cms:admin-settings-loaded', { detail: settings }));
        } catch (err) {
            console.warn('Admin settings could not be loaded:', err);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadAdminSettings);
    } else {
        loadAdminSettings();
    }
})();

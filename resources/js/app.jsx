import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route } from 'ziggy-js';

window.route = route;

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => {
        if (!title) return appName;
        const cleaned = title
            .replace(new RegExp(`(\\s*-\\s*${appName})+$`, 'i'), '')
            .replace(new RegExp(`^${appName}\\s*-\\s*`, 'i'), '')
            .trim();
        return `${cleaned} - ${appName}`;
    },
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#dc2626',
    },
});

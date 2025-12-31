import '../css/app.css';
import './bootstrap';
import BaseLayout from './Layouts/BaseLayout.vue';
import fontawesome from './Plugins/fontawesome.js';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        );

        page.then((module) => {
            const component = module.default;
            const isAuthPage = name.startsWith('Auth/');
            const isWelcomePage = name === 'Welcome';

            if (!component.layout && !isAuthPage && !isWelcomePage) {
                component.layout = BaseLayout;
            }
        });

        return page;
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(fontawesome)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

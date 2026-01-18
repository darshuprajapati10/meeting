import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    title: (title) => title ? `${title} - YUJIX` : 'YUJIX - Meeting Management',
    resolve: async (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: false });
        
        // Try exact match first
        let path = `./Pages/${name}.vue`;
        
        // If not found, try with different path variations
        if (!pages[path]) {
            // Try nested path (e.g., Auth/Login)
            const parts = name.split('/');
            if (parts.length > 1) {
                path = `./Pages/${parts.join('/')}.vue`;
            }
        }
        
        if (!pages[path]) {
            // Try case-insensitive search
            const normalizedName = name.toLowerCase();
            const found = Object.keys(pages).find(key => 
                key.toLowerCase().includes(normalizedName.toLowerCase())
            );
            if (found) {
                path = found;
            }
        }
        
        if (!pages[path]) {
            console.error(`Page not found: ${name}`);
            console.error('Available pages:', Object.keys(pages));
            throw new Error(`Page not found: ${name}`);
        }
        
        try {
            const module = await pages[path]();
            return module.default || module;
        } catch (error) {
            console.error(`Error loading page ${name} from ${path}:`, error);
            throw error;
        }
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#415E72',
        showSpinner: true,
    },
});

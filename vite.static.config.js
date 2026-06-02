import { defineConfig } from 'vite';

export default defineConfig({
    root: 'netlify',
    publicDir: 'public',
    build: {
        outDir: '../dist',
        emptyOutDir: true,
    },
    server: {
        host: '0.0.0.0',
        port: 5174,
    },
});

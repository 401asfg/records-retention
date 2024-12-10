import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), "");
    return {
        define: {
            'process.env.PUBLIC_URL': JSON.stringify(env.PUBLIC_URL)
        },
        server: {
            host: "0.0.0.0",
            hmr: {
                host: 'localhost'
            },
            port: 3000,
        },
        plugins: [
            laravel({
                input: ["resources/js/app.jsx"],
                refresh: true,
            }),
            react(),
        ],
    }
});

import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react-swc';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  cacheDir: 'node_modules/.vite',
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:81/',
        changeOrigin: true,
      },
    },
  },
});

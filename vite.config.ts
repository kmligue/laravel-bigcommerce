import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'dist',
    manifest: 'manifest.json',
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(__dirname, 'resources/js/main.tsx'),
    },
  },
});

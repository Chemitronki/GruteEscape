import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
  build: {
    // Code splitting for better performance
    rollupOptions: {
      output: {
        manualChunks: {
          'react-vendor': ['react', 'react-dom', 'react-router-dom'],
          'redux-vendor': ['@reduxjs/toolkit', 'react-redux'],
          'puzzles': [
            './src/components/game/puzzles/SymbolCipher.jsx',
            './src/components/game/puzzles/RitualPattern.jsx',
            './src/components/game/puzzles/AncientLock.jsx',
            './src/components/game/puzzles/MemoryFragments.jsx',
            './src/components/game/puzzles/CosmicAlignment.jsx',
            './src/components/game/puzzles/TentacleMaze.jsx',
            './src/components/game/puzzles/ForbiddenTome.jsx',
            './src/components/game/puzzles/ShadowReflection.jsx',
            './src/components/game/puzzles/CultistCode.jsx',
            './src/components/game/puzzles/ElderSignDrawing.jsx',
          ],
        },
      },
    },
    // Optimize chunk size
    chunkSizeWarningLimit: 1000,
    // Minify for production
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
      },
    },
  },
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './src/test/setup.js',
  },
});

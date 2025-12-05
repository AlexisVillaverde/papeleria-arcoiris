import { defineConfig } from 'vite'

export default defineConfig({
  root: './',
  server: {
    port: 3000,
    open: true,
    proxy: {
      '/database': {
        target: 'http://localhost',
        changeOrigin: true,
        secure: false
      }
    }
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets'
  }
})
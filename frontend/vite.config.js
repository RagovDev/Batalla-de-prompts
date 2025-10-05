import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss()
  ],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:3000', // backend Node/Express
        changeOrigin: true,
      },
      '/uploads': {
        target: 'http://localhost:3000', // para servir imágenes
        changeOrigin: true,
      }
    }
  }
})

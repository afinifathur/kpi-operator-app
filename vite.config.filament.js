import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/filament/app.css', 'resources/filament/app.js'],
      refresh: true,
    }),
  ],
})

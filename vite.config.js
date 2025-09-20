// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/filament/app.css',
        'resources/filament/app.js',
        'resources/js/chart-dashboard.js', // ⬅️ tambah ini
		'resources/css/filament/admin/theme.css', // ← penting
      ],
      refresh: true,
    }),
  ],
})

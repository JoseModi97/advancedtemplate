import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  base: '/elegance-beauty-parlour/', // MUST match folder name in htdocs
  plugins: [react()],
});

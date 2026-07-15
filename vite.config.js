import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { networkInterfaces } from 'os';

const getLocalIp = () => {
  const nets = networkInterfaces();
  let fallbackIp = '0.0.0.0';

  for (const name of Object.keys(nets)) {
    // I-skip ang VirtualBox, VMware, at WSL adapters
    if (
      name.toLowerCase().includes('vbox') ||
      name.toLowerCase().includes('vethernet') ||
      name.toLowerCase().includes('wsl')
    ) {
      continue;
    }

    for (const net of nets[name]) {
      // Piliin lang ang IPv4, hindi internal, at dapat nagsisimula sa 192.168
      if (net.family === 'IPv4' && !net.internal && net.address.startsWith('192.168')) {
        return net.address;
      }
    }
  }
  return fallbackIp;
};

const localIp = getLocalIp();

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.jsx'],
      refresh: true,
    }),
    react(),
    tailwindcss(),
  ],
  server: {
    host: '0.0.0.0',
    cors: true,
    hmr: {
      host: localIp,
    },
  },
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
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
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
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

// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';
// import tailwindcss from '@tailwindcss/vite';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//         tailwindcss(),
//     ],
//     server: {
//         host: '192.168.1.4', // Allows connections from other devices
//         cors: true, // <--- ADD THIS LINE to fix the CORS error
//         hmr: {
//             host: '192.168.1.4', // Your IPv4 address
//         },
//         watch: {
//             usePolling: true, // This ensures Windows catches the "Save" event
//             ignored: ['**/storage/framework/views/**'],
//         },
//     },
// });

// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';
// import tailwindcss from '@tailwindcss/vite';
// import { networkInterfaces } from 'os';

// // Function to find your local IPv4 address automatically
// const getLocalIp = () => {
//     const nets = networkInterfaces();
//     for (const name of Object.keys(nets)) {
//         for (const net of nets[name]) {
//             // Skip over non-IPv4 and internal (i.e. 127.0.0.1) addresses
//             if (net.family === 'IPv4' && !net.internal) {
//                 return net.address;
//             }
//         }
//     }
//     return '0.0.0.0'; // Fallback
// };

// const localIp = getLocalIp();

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//         tailwindcss(),
//     ],
//     server: {
//         host: '0.0.0.0', // Listens on all interfaces (crucial for mobile)
//         cors: true,
//         hmr: {
//             host: localIp, // Automatically uses your current 192.168.x.x
//         },
//         watch: {
//             usePolling: true,
//             ignored: ['**/storage/framework/views/**'],
//         },
//     },
// });

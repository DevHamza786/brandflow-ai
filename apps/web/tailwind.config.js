/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        surface: {
          DEFAULT: '#0c0f14',
          raised: '#12161e',
          overlay: '#181d27',
        },
        border: {
          DEFAULT: '#252b36',
          muted: '#1a1f28',
        },
        accent: {
          DEFAULT: '#3b9eff',
          muted: '#2563a8',
          glow: 'rgba(59, 158, 255, 0.15)',
        },
        status: {
          queued: '#94a3b8',
          running: '#3b9eff',
          completed: '#34d399',
          failed: '#f87171',
        },
      },
      fontFamily: {
        sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
      },
      boxShadow: {
        glow: '0 0 40px -10px rgba(59, 158, 255, 0.35)',
      },
    },
  },
  plugins: [],
};

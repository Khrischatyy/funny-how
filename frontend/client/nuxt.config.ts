// https://nuxt.com/docs/api/configuration/nuxt-config
import { fileURLToPath, URL } from 'url'
export default defineNuxtConfig({
  app: {
    head: {
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1',
    }
  },
  dir: {
    app: fileURLToPath(new URL('./src/app', import.meta.url)),
    entities: fileURLToPath(new URL('./src/entities', import.meta.url)),
    features: fileURLToPath(new URL('./src/features', import.meta.url)),
    pages: fileURLToPath(new URL('./src/pages', import.meta.url)),
    shared: fileURLToPath(new URL('./src/shared', import.meta.url)),
    widgets: fileURLToPath(new URL('./src/widgets', import.meta.url))
  },
  components: {
    dirs: []
  },
  devtools: {enabled: true},
  imports: {
    autoImport: false
  },
  modules: [
    '@pinia/nuxt',
    '@vueuse/nuxt',
  ],
  nitro: {
    routeRules: {
      '/api/**': {
        proxy: process.env.NUXT_PUBLIC_API_BASE,
      },
    },
  },
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
  runtimeConfig: {
    public: {
      apiBase: process.env.AXIOS_BASEURL,
    }
  },
  tailwindcss: {
  	cssPath: '@/src/shared/assets/style/tailwind.css',
  },
  css: ['@/src/shared/assets/style/index.scss'],
})

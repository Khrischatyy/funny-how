// https://nuxt.com/docs/api/configuration/nuxt-config
import { fileURLToPath, URL } from 'url'
export default defineNuxtConfig({
  security: {
    headers: {
      contentSecurityPolicy: {
        'default-src': ["'self'", "'unsafe-inline'", "data:", "https://maps.googleapis.com"]
      }
    }
  },
  app: {
    head: {
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1',
    },
    pageTransition: { name: 'page', mode: 'out-in'}
  },
  build: {
    transpile: ['@googlemaps/js-api-loader'],
  },
  dir: {
    app: fileURLToPath(new URL('./src/app', import.meta.url)),
    entities: fileURLToPath(new URL('./src/entities', import.meta.url)),
    features: fileURLToPath(new URL('./src/features', import.meta.url)),
    pages: fileURLToPath(new URL('./src/pages', import.meta.url)),
    shared: fileURLToPath(new URL('./src/shared', import.meta.url)),
    widgets: fileURLToPath(new URL('./src/widgets', import.meta.url)),
    middleware: fileURLToPath(new URL('./src/middleware', import.meta.url))
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

  vueTransitions: {
    name: 'slide',
    duration: 500,
    mode: 'out-in',
  },
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
      googleMapKey: process.env.GOOGLE_MAPS_API,
      googlePlacesApi: process.env.GOOGLE_PLACES_API,
      apiBase: process.env.AXIOS_BASEURL,
    }
  },
  tailwindcss: {
  	cssPath: '@/src/shared/assets/style/tailwind.css',
  },
  css: ['@/src/shared/assets/style/index.scss', 'animate.css/animate.min.css'],

})

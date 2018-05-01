module.exports = {

  /*
  ** Headers of the page
  */
  head: {
    title: 'AnimeTrends',
    titleTemplate: 'AnimeTrends - %s',
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: 'Nuxt.js project' }
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
    ]
  },

  /*
  ** Customize the progress bar color
  */
  loading: {
    color: '#03A9F4',
    height: '5px'
  },

  css: [
      '~scss/global.scss'
  ],

  /*
  ** Build configuration
  */
  build: {
    /*
    ** Run ESLint on save
    */
    extend (config, { isDev, isClient }) {
      if (isDev && isClient) {
        config.module.rules.push({
          enforce: 'pre',
          test: /\.(js|vue)$/,
          loader: 'eslint-loader',
          exclude: /(node_modules)/
        })
      }
    }
  },

  // env: {
  //   apiBaseUrl: process.env.NODE_ENV !== 'production' ? 'http://localhost:8000/api' : '/api',
  //   backendBaseUrl: process.env.NODE_ENV !== 'production' ? 'http://localhost:8000/' : '/',
  // },

  modules: [
    '@nuxtjs/dotenv'
  ],

  plugins: [
    '~/plugins/axios-defaults',
    '~/plugins/filter-suffixed-number',
    '~/plugins/filter-round',
    '~/plugins/filter-slugify',

    // { src: '~/plugins/highcharts', ssr: false },
  ],
}

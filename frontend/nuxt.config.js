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
      { hid: 'description', name: 'description', content: 'See MyAnimeList score charts of various anime on AnimeTrends!' }
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

  modules: [
    '@nuxtjs/dotenv'
  ],

  plugins: [
    '~/plugins/axios-defaults',
    '~/plugins/filter-suffixed-number',
    '~/plugins/filter-round',
    '~/plugins/filter-slugify',

    { src: '~/plugins/tracking-google-analytics', ssr: false },
  ],
}

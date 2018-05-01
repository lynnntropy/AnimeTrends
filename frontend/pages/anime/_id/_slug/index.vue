<template>
  <div class="anime-page">
    <div class="header-area">
      <AnimeInformation :anime="anime" />
      <section class="boost-warning" v-if="snapshotCount >= 5000">
        <strong>Note:</strong> This series has a more basic, simplified chart, due to the large amount of data we need to display for it.
      </section>
    </div>
    <section class="chart-area">
      <no-ssr>
        <Chart :anime-id="anime.id" />
      </no-ssr>
    </section>
  </div>
</template>

<script>
  import axios from 'axios'
  import AnimeInformation from '~/components/AnimeInformation.vue'
  import Chart from '~/components/Chart.vue'

  export default {

    components: {
      AnimeInformation,
      Chart
    },

    async asyncData ({ params }) {
      const { data } = await axios.get(`/anime/${params.id}`)
      return {
        anime: data.anime,
        snapshotCount: data.snapshotCount
      }
    },

    head () {
      return {
        title: this.anime.title,
        meta: [
          { hid: 'description', name: 'description', content: `MyAnimeList rating and popularity charts for ${this.anime.title}` },
          { property: "og:description", content: `MyAnimeList rating and popularity charts for ${this.anime.title}` },
        ],
      }
    }
  }
</script>

<style scoped lang="scss">
  .anime-page {
    display: grid;
    grid-template-rows: auto 1fr;

    .boost-warning {
      padding: 0.5rem 1rem;
      background-color: #fff3cd;
      color: #856404;
      border-bottom: 1px solid rgba(black, 0.05);

      strong {
        font-weight: 600;
      }
    }

    .chart-area {
    }
  }
</style>
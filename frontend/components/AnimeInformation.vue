<template>
  <section class="anime-information">
    <section class="image">
      <a :href="`https://myanimelist.net/anime/${anime.id}`" target="_blank" rel="noopener">
        <img :src="getImageUrl(anime.id)">
      </a>
    </section>
    <section class="details">
      <a :href="`https://myanimelist.net/anime/${anime.id}`" target="_blank" rel="noopener">
        <h1>{{ anime.title }}</h1>
      </a>
      <p>MyAnimeList members: <span class="members">{{ anime.members | suffixedNumber(1) }}</span></p>
      <p>Current score: <ColoredRating :rating="anime.rating" /></p>
    </section>
  </section>
</template>

<script>

  import ColoredRating from '~/components/ColoredRating.vue'

  export default {
    props: ['anime'],

    components: {
      ColoredRating
    },

    methods: {
      getImageUrl(animeId) {
        return `${process.env.BACKEND_BASE_URL}storage/cover_images/${animeId}.jpg`
      }
    }
  }
</script>

<style scoped lang="scss">

  @import "../scss/main";

  a {
    text-decoration: none;
  }

  .anime-information {
    display: grid;
    grid-template-columns: auto 1fr;
    grid-column-gap: 1rem;
    padding: 1rem;

    background: #fff;
    border-bottom: 1px solid #eee;

    .image {
      height: 4.75rem;
      min-width: 3rem;
      img {
        height: 100%;
        border-radius: 0.25rem;
      }

      @include breakpoint-md {
        height: 5.5rem;
        min-width: 3.75rem;
      }
    }

    .details {
      h1 {
        font-size: 1.1rem;
        color: #333;
        display: inline-block;

        @include breakpoint-md{
          font-size: 1.5rem;
        }
      }

      p {
        color: #666;
      }

      .colored-rating {
        background-color: #222;
        padding: 0 0.3rem;
        border-radius: 0.2rem;
      }

      .members {
        font-weight: 600;
      }
    }
  }
</style>
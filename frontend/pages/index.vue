<template>
  <section class="home">
    <section class="content">
      <h1>AnimeTrends</h1>

      <p>Welcome to AnimeTrends!</p>
      <p>This website allows anyone to observe how the scores and<br class="desktop-only">popularity of anime on MyAnimeList change over time.</p>
      <p>Pick a show from the list to get started.</p>
      <section class="mal-note">
        <p class="small">
          <strong>A note on MyAnimeList scores</strong>
        </p>
        <p class="small">
          MAL scores are an interesting gauge of public opinion for a certain segment of the anime community,
          but that's all they are. It's not a good idea to take them as an objective measure of a show's quality, or even just as a general indicator of public opinion.
        </p>
      </section>
      <section class="stats">
        <p>Database last updated <strong>{{ stats.diff }}</strong>.</p>
        <p>
          Working with a total of <strong>{{ stats.snapshot_count | suffixedNumber(1) }}</strong> data points
          for <strong>{{ stats.anime_count }}</strong> anime.
        </p>
      </section>
    </section>
  </section>
</template>

<script>
  import axios from 'axios'

  export default {

    async asyncData() {
      const { data } = await axios.get(`/stats`)
      return {
        stats: data
      }
    },

    head: {
      title: 'Home'
    }
  }
</script>

<style scoped lang="scss">

  @import '../scss/main';

  .desktop-only {
    display: none;

    @include breakpoint-md {
      display: inherit;
    }
  }

  .home {
    display: flex;
    align-items: center;
    justify-content: center;

    padding: 1rem;

    .content {
      text-align: center;
      max-width: 37rem;
    }

    h1 {
      font-size: 4rem;
      color: #222;
    }

    h2 {
      color: #222;
      font-size: 1.3rem;
      margin-top: 1.5rem;
      margin-bottom: 0;
    }

    p {
      margin-bottom: 0.5rem;
      color: #555;
      font-size: 1rem;

      @include breakpoint-md {
        font-size: 1.2rem;
      }

      strong {
        font-weight: 600;
      }

      &.small {
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
      }
    }

    .mal-note {
      margin-top: 2rem;
      background: #f7f7f7;
      border: 1px solid rgba(black, 0.05);
      text-align: left;
      padding: 0.5rem 1rem;
    }

    .stats {
      margin-top: 1rem;
      p {
        font-size: 14px;
        margin: 0;
        color: #888;
      }
    }
  }
</style>

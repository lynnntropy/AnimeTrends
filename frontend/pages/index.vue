<template>
  <section class="home">
    <section class="content">
      <h1>AnimeTrends</h1>

      <p>Welcome to AnimeTrends!</p>
      <p>The purpose of this website is to allow anyone to observe how the ratings and popularity of shows on MyAnimeList change over time.</p>
      <p>Pick a show from the list on the left to get started.</p>
    </section>
  </section>
</template>

<script>
  import axios from 'axios'

  export default {
    head: {
      title: 'Home'
    }
  }
</script>

<style scoped lang="scss">
  .home {
    display: flex;
    align-items: center;
    justify-content: center;

    .content {
      text-align: center;
      max-width: 30rem;
    }

    h1 {
      font-size: 4rem;
      color: #222;
    }

    p {
      margin-bottom: 0.5rem;
      color: #555;
      font-size: 1.2rem;
    }
  }
</style>

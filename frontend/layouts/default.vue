<template>
  <div class="main-layout">
    <Sidebar class="sidebar" />
    <nuxt class="nuxt" />
  </div>
</template>

<script>
  import Sidebar from '~/components/Sidebar.vue'
  export default {
    components: { Sidebar }
  }
</script>

<style lang="scss">

  @import '../scss/main';

  .main-layout {
    display: grid;

    .nuxt {
      grid-row: 1 / span 1;
    }


    @include breakpoint-md {
      min-height: 100vh;
      grid-template-columns: 20rem 1fr;

      .nuxt {
        grid-column: 2 / span 1;
      }
    }
  }
</style>

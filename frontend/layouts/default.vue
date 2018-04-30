<template>
  <div class="main-layout">
    <Sidebar />
    <nuxt/>
  </div>
</template>

<script>
  import Sidebar from '~/components/Sidebar.vue'
  export default {
    components: { Sidebar }
  }
</script>

<style lang="scss">
  .main-layout {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 20rem 1fr;
  }
</style>

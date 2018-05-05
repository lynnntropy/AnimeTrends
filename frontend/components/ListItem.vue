<template>
  <nuxt-link :to="`/anime/${item.id}/${$options.filters.slugify(item.title)}`">
    <div class="list-item" >
      <div class="title">{{ item.title }}</div>
      <div class="details">
        <ColoredRating :rating="item.rating" />
        &middot;
        {{ item.members | suffixedNumber(1) }} members
      </div>
    </div>
  </nuxt-link>
</template>

<script>
  import ColoredRating from '~/components/ColoredRating.vue'

  export default {
    props: ['item'],

    components: {
      ColoredRating
    }
  }
</script>

<style scoped lang="scss">
  .nuxt-link-active {
    .list-item {
      border-left-color: #fff;
    }
  }

  .list-item {
    padding: 0.5rem 0.5rem;
    border-left: 3px solid rgba(white, 0);

    transition: background-color .1s ease, border-left-color .2s ease;

    .title {
      color: #fff;
      font-size: 0.85rem;

      text-overflow: ellipsis;
      white-space: nowrap;
      overflow: hidden;
    }

    .details {
      font-size: 0.75rem;
      color: #777;
    }

    &:hover {
      background-color: rgba(white, 0.05);
    }
  }
</style>
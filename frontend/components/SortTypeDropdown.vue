<template>
  <div class="sort-type-dropdown" @click="dropdownOpen = !dropdownOpen">
    Sort by: <span class="current-sort">{{ currentType.name }}</span>
    <transition name="dropdown">
      <div class="dropdown" v-if="dropdownOpen">
        <div class="item" v-for="item in types" :key="item.name" @click="currentType = item">
          {{ item.name }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
  export default {
    props: ['types'],

    data () {
      return {
        dropdownOpen: false,
        currentType: this.types[0]
      }
    },

    watch: {
      currentType: function (newType) {
        this.$emit('change', newType)
      }
    }
  }
</script>

<style scoped lang="scss">

  .dropdown-enter-active, .dropdown-leave-active {
    transition: opacity .15s, transform .15s;
  }

  .dropdown-enter, .dropdown-leave-to {
    opacity: 0;
    transform: translateY(-0.35rem);
  }

  .sort-type-dropdown {
    position: relative;
    align-self: stretch;
    margin: 0.1rem 1.5rem 1rem 1.5rem;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;

    background: none;
    outline: none;
    color: #777;

    border-bottom: 1px solid rgba(white, 0.1);

    cursor: pointer;

    .current-sort {
      color: #fff;
    }

    .dropdown {
      z-index: 100;
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;

      padding: 0.25rem 0;

      background: #262626;
      color: #fff;

      box-shadow: 0 2px 10px rgba(black, 0.5);

      .item {
        padding: 0.5rem 0.75rem;

        background-color: rgba(white, 0);
        transition: background-color .1s ease;
        &:hover {
          background-color: rgba(white, 0.05);
        }
      }
    }
  }
</style>
<template>
  <nav class="sidebar">
    <nuxt-link to="/">
      <div class="header">
        AnimeTrends
      </div>
    </nuxt-link>
    <div class="search-container" @keydown.esc="filter = ''">
      <input class="search" type="text" placeholder="Search..." v-model="filter">
      <div class="clear-button" v-if="filter" @click="filter = ''">&times;</div>
    </div>
    <div class="tabs">
      <div class="tab" :class="{ active: currentTab === 'current' }" @click="currentTab = 'current'">
        Current <span class="count">({{ currentCount }})</span>
      </div>
      <div class="tab" :class="{ active: currentTab === 'archived' }" @click="currentTab = 'archived'">
        Archived <span class="count">({{ archivedCount }})</span>
      </div>
    </div>
    <transition mode="out-in" name="sidebar-transition">
      <div class="list-container" v-if="animeList.length > 0" key="list">
        <div class="anime-list">
          <nuxt-link :to="`/anime/${item.id}/${$options.filters.slugify(item.title)}`" v-for="item in filteredItems" :key="item.id">
            <div class="item" >
              <div class="title">{{ item.title }}</div>
              <div class="details">
                <ColoredRating :rating="item.rating" />
                &middot;
                {{ item.members | suffixedNumber(1) }} members
              </div>
            </div>
          </nuxt-link>
        </div>
      </div>
      <div class="loader-container" v-else key="loader">
        <SyncLoader color="rgba(255, 255, 255, 0.25)" size="10px" />
      </div>
    </transition>
  </nav>
</template>

<script>
  import axios from 'axios'
  import SyncLoader from 'vue-spinner/src/SyncLoader.vue'
  import ColoredRating from '~/components/ColoredRating.vue'

  export default {

    components: {
      SyncLoader,
      ColoredRating
    },

    data () {
      return {
        animeList: [],
        currentTab: 'current',
        filter: ''
      }
    },

    async mounted () {
      const { data } = await axios.get('/anime')
      this.animeList = data
    },

    computed: {
      filteredItems () {
        let filteredItems = this.animeList

        this.currentTab === 'archived'
          ? filteredItems = filteredItems.filter(item => item.archived === 1)
          : filteredItems = filteredItems.filter(item => item.archived === 0)

        if (this.filter)
          filteredItems = filteredItems.filter(item => item.title.toLowerCase().includes(this.filter.toLowerCase()))

        return filteredItems
      },

      currentCount () {
        return this.animeList.filter(item => item.archived === 0).length
      },

      archivedCount () {
        return this.animeList.filter(item => item.archived === 1).length
      }
    }
  }
</script>

<style scoped lang="scss">

  @import '../scss/main';

  .sidebar-transition-enter-active, .sidebar-transition-leave-active {
    transition: opacity .15s;
  }
  .sidebar-transition-enter, .sidebar-transition-leave-to {
    opacity: 0;
  }

  ::-webkit-scrollbar {
    width: 0.25rem;
    background: transparent;
  }

  ::-webkit-scrollbar-thumb {
    background: rgba(white, 0.35);
  }

  .sidebar {
    background: #222;
    color: #fff;

    display: flex;
    flex-direction: column;
    align-items: center;

    max-width: 100vw;

    @include breakpoint-md {
      max-height: 100vh;
    }



    .header {
      margin: 1rem;
      font-size: 2.5rem;
      color: #fff;

      transition: opacity .2s ease;

      &:hover {
        opacity: 0.75;
      }
    }

    .search-container {
      align-self: stretch;
      margin: 1rem 1.5rem;
      position: relative;

      .search {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 1rem;

        background: none;
        border: none;
        outline: none;
        color: #fff;

        border-bottom: 1px solid rgba(white, 0.1);

        transition: border-bottom-color 0.3s ease;

        &:focus {
          border-bottom-color: rgba(white, 0.3);
        }
      }

      .clear-button {
        position: absolute;
        right: 0.75rem;
        top: -0.4rem;
        font-size: 2rem;
        cursor: pointer;
        color: rgba(white, 0.35);
      }
    }


    .tabs {
      align-self: stretch;
      display: flex;
      flex-direction: row;
      flex-shrink: 0;

      .tab {
        flex: 1;
        padding: 0.5rem 0;
        text-align: center;
        border-bottom: 3px solid rgba(white, 0);

        cursor: pointer;
        font-size: 0.9rem;
        opacity: 0.5;

        transition: all .2s ease;

        .count {
          color: #777;
        }

        &:hover {
          opacity: 0.7;
          border-bottom-color: rgba(white, 0.1);
        }

        &.active {
          opacity: 1;
          border-bottom-color: rgba(white, 1);
        }
      }
    }

    .loader-container {
      height: 100%;
      min-height: 15rem;
      align-self: stretch;
      flex: 1 1 auto;

      display: flex;
      align-items: center;
      justify-content: center;
    }

    .list-container {
      align-self: stretch;
      flex: 1 1 auto;

      @include breakpoint-md {
        height: 100%;
      }

      overflow-y: auto;
      .anime-list {
        padding: 0.5rem 0;

        .nuxt-link-active {
          .item {
            border-left-color: #fff;
          }
        }

        .item {
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
      }
    }
  }
</style>
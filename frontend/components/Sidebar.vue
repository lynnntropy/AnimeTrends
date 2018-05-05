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
    <div class="tabs" :disabled="filter !== ''">
      <div class="tab" :class="{ active: currentTab === 'current' }" @click="(!filter) ? currentTab = 'current' : null">
        Current <span class="count">({{ currentCount }})</span>
      </div>
      <div class="tab" :class="{ active: currentTab === 'archived' }" @click="(!filter) ? currentTab = 'archived' : null">
        Archived <span class="count">({{ archivedCount }})</span>
      </div>
    </div>
    <transition mode="out-in" name="sidebar-transition">
      <div class="list-container" v-if="animeList.length > 0" key="list">
        <div class="anime-list">
          <div v-if="filter">
            <div v-if="filteredCurrentItems.length">
              <div class="list-header">CURRENT</div>
              <ListItem :item="item" v-for="item in filteredCurrentItems" :key="item.id" />
            </div>
            <div v-if="filteredArchivedItems.length">
              <div class="list-header">ARCHIVED</div>
              <ListItem :item="item" v-for="item in filteredArchivedItems" :key="item.id" />
            </div>
          </div>
          <div v-else>
            <ListItem :item="item" v-for="item in tabItems" :key="item.id" />
          </div>
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
  import ListItem from '~/components/ListItem.vue'

  export default {

    components: {
      SyncLoader,
      ListItem
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

      currentItems () {
        return this.animeList.filter(item => item.archived === 0)
      },

      archivedItems () {
        return this.animeList.filter(item => item.archived === 1)
      },

      tabItems () {
        return this.currentTab === 'archived'
          ? this.archivedItems
          : this.currentItems
      },

      filteredItems () {
        let filteredItems = this.animeList

        if (this.filter)
          filteredItems = filteredItems.filter(item => item.title.toLowerCase().includes(this.filter.toLowerCase()))

        return filteredItems
      },

      filteredCurrentItems () {
        return this.filteredItems.filter(item => item.archived === 0)
      },

      filteredArchivedItems () {
        return this.filteredItems.filter(item => item.archived === 1)
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

      &[disabled] {
        opacity: 0.25;
        cursor: not-allowed;

        .tab {
          cursor: inherit;
        }
      }

      &:not([disabled]) {
        .tab {
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

        .list-header {
          padding: 1rem 0.5rem 0.5rem;
          text-transform: uppercase;
          color: rgba(white, 0.35);
          font-weight: 600;
        }
      }
    }
  }
</style>
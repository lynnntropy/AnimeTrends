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

    <SortTypeDropdown :types="sortTypes" @change="handleSortTypeChange" />

    <div class="language-toggle-container">
      <toggle-button v-model="englishTitles" height="17" width="40"
                     :color="{checked: 'rgba(255, 255, 255, 0.25)', unchecked: 'rgba(255, 255, 255, 0.1)'}"
                      :sync="true"/>
      <p @click="englishTitles = !englishTitles">Prefer English titles</p>
    </div>

    <div class="tabs" :disabled="filterActive">
      <div class="tab" :class="{ active: currentTab === 'current' }" @click="(!filterActive) ? currentTab = 'current' : null">
        Current <span class="count">({{ counts.current }})</span>
      </div>
      <div class="tab" :class="{ active: currentTab === 'archived' }" @click="(!filterActive) ? currentTab = 'archived' : null">
        Archived <span class="count">({{ counts.archived }})</span>
      </div>
    </div>

    <transition mode="out-in" name="sidebar-transition">

      <div v-if="filterActive && !filteredLoading" class="list-container" key="filteredList">
        <div class="anime-list" v-if="this.anime.filtered.length">
          <div v-if="filteredCurrentItems.length">
            <div class="list-header">CURRENT</div>
            <ListItem :item="item" v-for="item in filteredCurrentItems" :key="item.id" :englishTitles="englishTitles"/>
          </div>
          <div v-if="filteredArchivedItems.length">
            <div class="list-header">ARCHIVED</div>
            <ListItem :item="item" v-for="item in filteredArchivedItems" :key="item.id" :englishTitles="englishTitles"/>
          </div>
        </div>
        <div v-else class="no-results-message">
          No results, sorry!
        </div>
      </div>

      <div v-else-if="!filterActive && anime.current.length && currentTab === 'current'" class="list-container" key="currentList" ref="currentContainer">
        <transition-group name="anime-list" tag="p">
          <ListItem class="anime-list-item" :item="item" v-for="item in anime.current" :key="item.id" :englishTitles="englishTitles"/>
        </transition-group>
        <mugen-scroll :style="{ opacity: anime.current.length < counts.current ? 1 : 0 }"
                      class="infinite-scroll" :handler="fetchMoreCurrent" :should-handle="shouldLoadMoreCurrent"
                      :scroll-container="isMobile ? null : 'currentContainer'">
          <SyncLoader color="rgba(255, 255, 255, 0.25)" size="10px" />
        </mugen-scroll>
      </div>

      <div v-else-if="!filterActive && anime.archived.length && currentTab === 'archived'" class="list-container" key="archivedList" ref="archivedContainer">
        <transition-group name="anime-list" tag="p">
          <ListItem class="anime-list-item" :item="item" v-for="item in anime.archived" :key="item.id" :englishTitles="englishTitles"/>
        </transition-group>
        <mugen-scroll :style="{ opacity: anime.archived.length < counts.archived ? 1 : 0 }"
                      class="infinite-scroll" :handler="fetchMoreArchived" :should-handle="shouldLoadMoreArchived"
                      :scroll-container="isMobile ? null : 'archivedContainer'">
          <SyncLoader color="rgba(255, 255, 255, 0.25)" size="10px" />
        </mugen-scroll>
      </div>

      <div v-else class="loader-container" key="loader">
        <SyncLoader color="rgba(255, 255, 255, 0.25)" size="10px" />
      </div>

    </transition>

  </nav>
</template>

<script>
  import axios from 'axios'
  import MugenScroll from 'vue-mugen-scroll'
  import SyncLoader from 'vue-spinner/src/SyncLoader.vue'
  import ListItem from '~/components/ListItem.vue'
  import SortTypeDropdown from '~/components/SortTypeDropdown.vue'

  const infiniteScrollChunkSize = 25
  let axiosCancelTokenSource = axios.CancelToken.source()

  export default {

    components: {
      MugenScroll,
      SyncLoader,
      ListItem,
      SortTypeDropdown
    },

    data () {
      return {

        anime: {
          current: [],
          archived: [],
          filtered: []
        },

        counts: {

        },

        sortTypes: [
          {
            name: 'Members (highest first)',
            property: 'members',
            order: 'desc'
          },
          {
            name: 'Members (lowest first)',
            property: 'members',
            order: 'asc'
          },
          {
            name: 'Score (highest first)',
            property: 'rating',
            order: 'desc'
          },
          {
            name: 'Score (lowest first)',
            property: 'rating',
            order: 'asc'
          },
          {
            name: 'Title (Z-A)',
            property: 'title',
            order: 'desc'
          },
          {
            name: 'Title (A-Z)',
            property: 'title',
            order: 'asc'
          }
        ],

        sortBy: 'members',
        sortOrder: 'desc',
        englishTitles: false,

        currentTab: 'current',
        filter: '',
        filteredLoading: false,
        infiniteScrollLoading: false
      }
    },

    mounted () {
      axios.get('/anime', {
        params: {
          archived: 0,
          limit: infiniteScrollChunkSize,
          sortBy: this.sortBy,
          sortOrder: this.sortOrder
        }
      })
      .then(({ data }) => {
        this.anime.current = data
      })

      axios.get('/anime', {
        params: {
          archived: 1,
          limit: infiniteScrollChunkSize,
          sortBy: this.sortBy,
          sortOrder: this.sortOrder
        }
      })
      .then(({ data }) => {
        this.anime.archived = data
      })

      axios.get('/counts').then(({ data }) => {
        this.counts = data
      })
    },

    watch: {
      filter: function () {
        this.refreshFiltered()
      }
    },

    methods: {

      fetchMoreArchived () {
        this.infiniteScrollLoading = true
        axios.get('/anime', {
          params: {
            archived: 1,
            offset: this.anime.archived.length,
            limit: infiniteScrollChunkSize,
            sortBy: this.sortBy,
            sortOrder: this.sortOrder
          }
        })
        .then(({ data }) => {
          this.infiniteScrollLoading = false
          this.anime.archived.push(...data)
        })
      },

      fetchMoreCurrent () {
        this.infiniteScrollLoading = true
        axios.get('/anime', {
          params: {
            archived: 0,
            offset: this.anime.current.length,
            limit: infiniteScrollChunkSize,
            sortBy: this.sortBy,
            sortOrder: this.sortOrder
          }
        })
        .then(({ data }) => {
          this.infiniteScrollLoading = false
          this.anime.current.push(...data)
        })
      },

      refreshFiltered () {
        if (this.filteredLoading) {
          axiosCancelTokenSource.cancel()
        }

        if (this.filterActive) {

          this.filteredLoading = true
          axiosCancelTokenSource = axios.CancelToken.source()

          axios.get('/anime', {
            params: {
              q: this.filter,
              sortBy: this.sortBy,
              sortOrder: this.sortOrder
            },
            cancelToken: axiosCancelTokenSource.token
          })
          .then(response => {
            this.anime.filtered = response.data
            this.filteredLoading = false
          })
          .catch(() => {})
        }
      },

      reloadAll() {
        this.anime.current = []
        this.anime.archived = []
        this.anime.filtered = []

        this.fetchMoreCurrent()
        this.fetchMoreArchived()
        this.refreshFiltered()
      },

      handleSortTypeChange (newType) {
        this.sortBy = newType.property
        this.sortOrder = newType.order
        this.reloadAll()
      }
    },

    computed: {

      isMobile () {
        return window.innerWidth < 768
      },

      shouldLoadMoreCurrent () {
        return !this.infiniteScrollLoading && (this.anime.current.length < this.counts.current)
      },

      shouldLoadMoreArchived () {
        return !this.infiniteScrollLoading && (this.anime.archived.length < this.counts.archived)
      },

      filterActive () {
        return (this.filter.length >= 2)
      },

      filteredCurrentItems () {
        return this.anime.filtered.filter(item => item.archived === 0)
      },

      filteredArchivedItems () {
        return this.anime.filtered.filter(item => item.archived === 1)
      },
    }
  }
</script>

<style scoped lang="scss">

  @import '../scss/main';

  .sidebar-transition-enter-active, .sidebar-transition-leave-active {
    transition: opacity .15s, transform .15s;
  }
  .sidebar-transition-enter, .sidebar-transition-leave-to {
    opacity: 0;
    transform: translateY(0.35rem);
  }

  .anime-list-item {
    display: inline-block;
    width: 100%;
    margin-right: 10px;
  }

  .anime-list-enter-active, .anime-list-leave-active, .anime-list-item {
    transition: all 0.25s;
  }

  .anime-list-enter, .anime-list-leave-to {
    opacity: 0;
    transform: translateY(20px);
  }

  ::-webkit-scrollbar {
    width: 0.25rem;
    background: transparent;
  }

  ::-webkit-scrollbar-thumb {
    background: rgba(white, 0.35);
  }

  .no-results-message {
    text-align: center;
    padding: 1rem;
    color: #777;
  }

  .sidebar {
    background: #222;
    color: #fff;

    display: flex;
    flex-direction: column;
    align-items: center;
    overflow: hidden;

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

    .language-toggle-container {
      align-self: stretch;
      margin: 0.1rem 1.5rem 1rem 1.5rem;
      display: flex;
      align-items: center;
      flex-shrink: 0;

      p {
        margin-left: 0.5rem;
        cursor: pointer;
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
      overflow-x: hidden;
      padding: 0.5rem 0;

      @include breakpoint-md {
        height: 100%;
      }

      overflow-y: auto;
      .anime-list {
        .list-header {
          padding: 1rem 0.5rem 0.5rem;
          text-transform: uppercase;
          color: rgba(white, 0.35);
          font-weight: 600;
        }
      }

      .infinite-scroll {
        text-align: center;
        padding: 1.5rem 0 5rem 0;
      }
    }
  }
</style>
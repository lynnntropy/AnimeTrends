<template>
  <span class="colored-rating" :style="{ color: getRatingColor(rating) }">
    {{ rating | round(2) }}
  </span>
</template>

<script>
  export default {

    props: ['rating'],

    methods: {
      getRatingColor(rating) {
        if (rating >= 9) return '#2196F3'
        if (rating >= 8) return '#4CAF50'
        if (rating >= 7.5) return '#8BC34A'
        if (rating >= 7) return '#CDDC39'
        if (rating >= 6.8) return '#FFEB3B'
        if (rating >= 6.5) return '#FFC107'
        if (rating >= 6) return '#FF9800'
        return '#FF5722'
      }
    }
  }
</script>
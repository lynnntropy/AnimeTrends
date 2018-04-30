import Vue from 'vue'

Vue.filter('round', (value, accuracy) => value.toFixed(accuracy))
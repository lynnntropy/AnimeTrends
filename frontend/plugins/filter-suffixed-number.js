import Vue from 'vue'

Vue.filter('suffixedNumber', (value, decimals) => {
  let exp, rounded,
    suffixes = ['k', 'M', 'G', 'T', 'P', 'E'];

  if(isNaN(value)) {
    return null;
  }

  if(value < 1000) {
    return value;
  }

  exp = Math.floor(Math.log(value) / Math.log(1000));

  return (value / Math.pow(1000, exp)).toFixed(decimals) + suffixes[exp - 1];
})
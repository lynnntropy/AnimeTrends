<template>
  <div class="chart-container">
    <div v-if="chartLoaded" id="highcharts-container">
    </div>
    <div v-else class="loading-container">
      <ScaleLoader class="loading-animation" color="#999" size="20px" />
      <p>Loading chart...</p>
    </div>
  </div>
</template>

<script>
  import Highcharts from 'highcharts'

  if (process.browser) {
    const Boost  = require('highcharts/modules/boost')
    Boost(Highcharts)
  }

  import axios from 'axios'
  import ScaleLoader from 'vue-spinner/src/ScaleLoader.vue'

  export default {
    props: ['animeId'],

    components: {
      ScaleLoader
    },

    data () {
      return {
        chartLoaded: false
      }
    },

    async mounted () {
      const { data } = await axios.get(`/anime/${this.animeId}/history`)
      this.initializeChart(data)
    },

    methods: {

      mysqlToIsoDateTime: (dateTimeString) => dateTimeString.replace(' ', 'T') + "+00:00",

      initializeChart (data) {

        // Define the arrays that will hold the series data in the format Highcharts expects.

        const ratingData = [];
        const membersData = [];

        // Parse the raw snapshots and populate the arrays.

        for (let i = 0; i < data.length; i++) {
          ratingData.push([Date.parse(this.mysqlToIsoDateTime(data[i].timestamp)), data[i].rating]);
          membersData.push([Date.parse(this.mysqlToIsoDateTime(data[i].timestamp)), data[i].members]);
        }

        const chartOptions = {
          chart: {
            type: 'line',
            backgroundColor: null,
            style: {
              fontFamily: "'Titillium Web', sans-serif"
            }
          },
          title: {text: ''},
          xAxis: {
            id: 'datetime-axis',
            type: 'datetime',
            ordinal: false,
            title: {
              text: 'Date/Time (UTC)'
            },
            units: [[
              'day',
              [1]
            ], [
              'week',
              [1]
            ], [
              'month',
              [1, 3, 6]
            ], [
              'year',
              null
            ]]
          },
          yAxis: [{
            id: 'rating-axis',
            title: {
              text: 'Score'
            },
            softMin: 7,
            softMax: 9,
            opposite: true,
          }, {
            id: 'members-axis',
            title: {
              text: 'Members'
            },
            min: 0
          }],

          plotOptions: {
            series: {
              lineWidth: 4,
              animation: false
            }
          },

          series: [{
            id: 'rating-series',
            name: 'Score',
            yAxis: 0,
            data: ratingData,
            tooltip: {
              headerFormat: '<span style="font-size: 10px">{point.x:%b %e, %H:%M (UTC)}</span><br>',
              pointFormat: '<span style="color:{point.color}">\u25CF</span> Score: <b>{point.y:.2f}</b><br>',
              // crosshairs: [true],
              // padding: 50
            },


            dataLabels: {
              enabled: true,
              padding: 10,
              backgroundColor: 'rgba(0, 0, 0, 0)',
              style: {
                color: 'rgba(0, 0, 0, 0.3)',
                fontSize: '14px',
                textOutline: '5px #fafafa'
              },
              formatter: function() {
                return this.y.toFixed(2)
              }
            },

            zones: [{
              value: 6,
              color: '#FF5722'
            }, {
              value: 6.5,
              color: '#FF9800'
            }, {
              value: 7,
              color: '#FFC107'
            }, {
              value: 7.5,
              color: '#CDDC39'
            }, {
              value: 8,
              color: '#8BC34A'
            }, {
              value: 9,
              color: '#4CAF50'
            }, {
              color: '#2196F3'
            }]

          }, {
            id: 'members-series',
            name: 'Members',
            yAxis: 1,
            data: membersData,
            tooltip: {
              headerFormat: '<span style="font-size: 10px">{point.x:%b %e, %H:%M (UTC)}</span><br>'
            },

            marker: {
              enabled: false
            },

            dashStyle: 'Dash',
            color: membersData.length > 5000 ? null : 'rgba(0, 0, 0, 0.25)'
          }]
        }

        this.chartLoaded = true

        // Wait for Vue to render the container div to the DOM.
        this.$nextTick().then(() => {
          // Initialize the chart.
          Highcharts.chart('highcharts-container', chartOptions)
        })
      },

    }
  }
</script>

<style scoped lang="scss">

  @import '../scss/main';

  .chart-container  {
    width: 100%;
    height: 100%;

    #highcharts-container {
      width: 100%;
      height: calc(100% - 2rem);
      margin-top: 2rem;
      font-family: $primary-font !important;
    }

    .loading-container {
      width: 100%;
      height: 100%;

      display: flex;
      align-items: center;
      justify-content: center;

      min-height: 15rem;

      .loading-animation {
        position: relative;
        top: 5px;
        margin-right: 1rem;
      }

      p {
        color: #999;
        font-size: 1.75rem;
      }
    }
  }
</style>
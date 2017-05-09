Chart.pluginService.register({
    afterDraw: function(chartInstance) {
        var ctx = chartInstance.chart.ctx;

        // render the value of the chart above the bar
        ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, 'normal', Chart.defaults.global.defaultFontFamily);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';

        chartInstance.data.datasets.forEach(function (dataset) {
            for (var i = 0; i < dataset.data.length; i++) {
                var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model;
                ctx.fillText(dataset.data[i], model.x, model.y - 2);
            }
        });
    }
});

angular.module('animeStocks', ['ngRoute', 'chart.js', 'ng-fusioncharts'])

    .factory('backendService', function ($http) {

        var getAnimeList = function()
        {
            return $http.get('/api/anime').then(function (res) {
                return res.data;
            });
        };

        var getHistoryForAnime = function(animeId)
        {
            return $http.get('/api/anime/' + animeId + '/history').then(function (res) {
                return res.data;
            });
        };

        var getAnime = function(animeId)
        {
            return $http.get('/api/anime/' + animeId).then(function (res) {
                return res.data;
            });
        };

        return {
            getAnimeList: getAnimeList,
            getHistoryForAnime: getHistoryForAnime,
            getAnime: getAnime
        }
    })

    .controller('SidebarController', function($scope, $rootScope, backendService) {
        backendService.getAnimeList().then(function (data) {
            console.log(data);
            $scope.anime = data;
        });

        $scope.selectAnime = function(anime)
        {
            $rootScope.selectedAnime = anime;
        }
    })

    .controller('HomeController', function() {})

    .controller('AnimeController', function($scope, $rootScope, $route, backendService) {

        $scope.dataSource = {
            "chart": {
                "caption": "Column Chart Built in Angular!",
                "captionFontSize": "30",
                // more chart properties - explained later
            },
            "data": [{
                "label": "CornflowerBlue",
                "value": "42"
            } //more chart data
            ]
        };

        $scope.data = [[], []];
        $scope.labels = [];
        $scope.series = ['Average Rating', 'Members'];
        $scope.datasetOverride = [
            {
                yAxisID: 'y-axis-1',
                lineTension: 0
            },
            {
                yAxisID: 'y-axis-2',
                lineTension: 0
            }];
        $scope.colors = ['#2196f3', '#F44336', '#717984', '#F1C40F'];
        $scope.options = {
            scales: {
                yAxes: [
                    {
                        id: 'y-axis-1',
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            min: 1,
                            max: 10
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Average Rating'
                        }
                    },
                    {
                        id: 'y-axis-2',
                        type: 'linear',
                        display: true,
                        position: 'right',
                        gridLines: {
                            display:false
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Members'
                        }
                    }
                ]
            },

            legend: {
                display: true
            }
        };

        backendService.getHistoryForAnime($route.current.params['animeId']).then(function (data) {
            console.log(data);
            data.forEach(function(snapshot) {
                $scope.data[0].push(snapshot.rating);
                $scope.data[1].push(snapshot.members);
                $scope.labels.push(snapshot.created_at);
            });
        });

        if (!$rootScope.selectedAnime)
        {
            backendService.getAnime($route.current.params['animeId']).then(function (data) {
                $rootScope.selectedAnime = data;
            });
        }

        Chart.defaults.global.colors
    })

    .config(function($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'home.html',
                controller: 'HomeController'
            })
            .when('/anime/:animeId', {
                templateUrl: 'anime.html',
                controller: 'AnimeController'
            });

        $locationProvider.html5Mode(true);
    });

angular.module('Utils', [])
    .filter('thousandSuffix', function () {
        return function (input, decimals) {
            var exp, rounded,
                suffixes = ['k', 'M', 'G', 'T', 'P', 'E'];

            if(window.isNaN(input)) {
                return null;
            }

            if(input < 1000) {
                return input;
            }

            exp = Math.floor(Math.log(input) / Math.log(1000));

            return (input / Math.pow(1000, exp)).toFixed(decimals) + suffixes[exp - 1];
        };
    });

angular.module('animeStocks', ['ngRoute', 'highcharts-ng', 'slugifier', 'Utils'])

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
        $scope.ratingData = [];
        $scope.membersData = [];

        $scope.chartConfig = {
            chart: {
                type: 'spline',
                width: $("div[ng-view]").width(),
                height: $("div[ng-view]").height() - 130,
                panning: true
            },
            title: {text: ''},
            xAxis: {
                type: 'datetime',
                title: {
                    text: 'Date/Time (UTC)'
                }
            },
            yAxis: [{
                title: {
                    text: 'Average Rating'
                },
                softMin: 4.5,
                // tickInterval: 0.5,
                max: 10,
                ceiling: 10
            }, {
                title: {
                    text: 'Members (popularity)'
                },
                min: 0,
                opposite: true
            }],
            plotOptions: {
                spline: {
                    marker: {
                        enabled: true
                    },
                    dataLabels: {
                        enabled: true
                    }
                }
            },

            // navigator: {
            //     enabled: true
            // },

            series: [{
                name: 'Average Rating',
                yAxis: 0,
                data: $scope.ratingData,
                tooltip: {
                    headerFormat: '<b>{series.name}</b><br>',
                    pointFormat: '{point.x:%b %e}: {point.y:.2f}'
                },
                plotOptions: {
                    spline: {
                        dataLabels: {
                            enabled: true,
                            format: '{point.y} mm'
                        }
                    }
                }
            }, {
                name: 'Members (popularity)',
                yAxis: 1,
                data: $scope.membersData,
                tooltip: {
                    headerFormat: '<b>{series.name}</b><br>',
                    pointFormat: '{point.x:%b %e}: {point.y:.0f}'
                }
            }]
        };


        if (!$rootScope.selectedAnime)
        {
            backendService.getAnime($route.current.params['animeId']).then(function (data) {
                $rootScope.selectedAnime = data;
            });
        }

        backendService.getHistoryForAnime($route.current.params['animeId']).then(function (data) {
            data.forEach(function(snapshot) {
                $scope.ratingData.push([Date.parse(snapshot.created_at), snapshot.rating]);
                $scope.membersData.push([Date.parse(snapshot.created_at), snapshot.members]);
            });
        });
    })

    .config(function($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'home.html',
                controller: 'HomeController'
            })
            .when('/anime/:animeId/:animeSlug?', {
                templateUrl: 'anime.html',
                controller: 'AnimeController'
            });

        $locationProvider.html5Mode(true);
    });

function mysqlToIsoDateTime(dateTimeString)
{
    return dateTimeString.replace(' ', 'T') + "+00:00";
}

angular.module('animeStocks', ['ngRoute', 'highcharts-ng', 'slugifier', 'angular-google-analytics'])

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
    })

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
        $rootScope.loading = true;

        backendService.getAnimeList().then(function (data) {
            $scope.anime = data;
            $rootScope.loading = false;
        });

        $scope.selectAnime = function(anime)
        {
            $rootScope.selectedAnime = anime;
        }
    })

    .controller('HomeController', function() {})

    .controller('AnimeController', function($scope, $rootScope, $route, thousandSuffixFilter, backendService) {
        $scope.ratingData = [];
        $scope.membersData = [];
        $scope.episodePlotLines = [];
        $scope.loading = true;

        $scope.chartConfig = {
            rangeSelector: {
                enabled: true,
                selected: 4,
                buttons: [{
                    type: 'month',
                    count: 3,
                    text: '3m'
                }, {
                    type: 'month',
                    count: 1,
                    text: '1m'
                }, {
                    type: 'week',
                    count: 1,
                    text: '1w'
                }, {
                    type: 'all',
                    text: 'All'
                }]
            },
            chart: {
                chartType: 'stock',
                width: $("div[ng-view]").width(),
                height: $("div[ng-view]").height() - $("div.anime-header").outerHeight(true),
                panning: true
            },
            title: {text: ''},
            xAxis: {
                id: 'datetime-axis',
                type: 'datetime',
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
                ]],
                plotLines: $scope.episodePlotLines,
                crosshair: true,
                labels: {
                    format: '{value:%b %e}'
                }
            },
            yAxis: [{
                id: 'rating-axis',
                title: {
                    text: 'Average Rating'
                },
                softMin: 5.5,
                softMax: 10
            }, {
                id: 'members-axis',
                title: {
                    text: 'Members (popularity)'
                },
                min: 0,
                opposite: true
            }],

            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true,
                        style: {
                            "opacity": 0.3
                        },
                        formatter: function() {
                            return thousandSuffixFilter(this.y, 1)
                        }
                    }
                }
            },

            navigator: {
                enabled: true,
                xAxis: {
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
                }
            },

            series: [{
                id: 'rating-series',
                name: 'Average Rating',
                yAxis: 0,
                data: $scope.ratingData,
                tooltip: {
                    headerFormat: '<b>{series.name}</b><br>',
                    pointFormat: '{point.x:%H:%M (UTC) on %b %e}: <b>{point.y:.2f}</b>',
                    crosshairs: [true]
                },
                plotOptions: {
                }
            }, {
                id: 'members-series',
                name: 'Members (popularity)',
                yAxis: 1,
                data: $scope.membersData,
                tooltip: {
                    headerFormat: '<b>{series.name}</b><br>',
                    pointFormat: '{point.x:%H:%M (UTC) on %b %e}: <b>{point.y:,.0f}</b>'
                }
            }],

            colors: ['#2196F3', '#FFC107', '#90ed7d', '#f7a35c', '#8085e9',
                '#f15c80', '#e4d354', '#8085e8', '#8d4653', '#91e8e1']
        };


        if (!$rootScope.selectedAnime)
        {
            backendService.getAnime($route.current.params['animeId']).then(function (data) {
                $rootScope.selectedAnime = data;
                $rootScope.title = $rootScope.selectedAnime.title;
            });
        }
        else
        {
            $rootScope.title = $rootScope.selectedAnime.title;
        }

        backendService.getHistoryForAnime($route.current.params['animeId']).then(function (data) {
            data.forEach(function(snapshot) {
                $scope.ratingData.push([Date.parse(mysqlToIsoDateTime(snapshot.created_at)), snapshot.rating]);
                $scope.membersData.push([Date.parse(mysqlToIsoDateTime(snapshot.created_at)), snapshot.members]);
            });

            var weekInMilliseconds = 7 * 24 * 60 * 60 * 1000;
            for(var i = $rootScope.selectedAnime.start * 1000; i < Date.now() + weekInMilliseconds * 3; i += weekInMilliseconds)
            {
                console.log("Adding plot line at: " + i);
                $scope.episodePlotLines.push({
                    color: '#8BC34A',
                    dashStyle: 'Dash',
                    width: 1,
                    value: i,
                    label: {
                        text: 'New<br>episode<br>(Japan)',
                        rotation: 0,
                        verticalAlign: 'bottom',
                        y: -50,
                        style: {
                            'opacity': 0.7
                        }
                    }});
            }


            $scope.loading = false;
        });

        $scope.deselectAnime = function()
        {
            $rootScope.selectedAnime = null;
        }
    })

    .config(['AnalyticsProvider', function (AnalyticsProvider) {
        AnalyticsProvider.setAccount('UA-25952845-5');
    }]).run(['Analytics', function(Analytics) { }])

    .config(function($routeProvider, $locationProvider) {
        $routeProvider
            .when('/', {
                title: 'Home',
                templateUrl: 'home.html',
                controller: 'HomeController'
            })
            .when('/anime/:animeId/:animeSlug?', {
                title: 'Loading...',
                templateUrl: 'anime.html',
                controller: 'AnimeController'
            });

        $locationProvider.html5Mode(true);
    })

    .run(['$rootScope', function($rootScope) {
        $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
            $rootScope.title = current.$$route.title;
        });
    }]);

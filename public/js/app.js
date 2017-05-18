function mysqlToIsoDateTime(dateTimeString)
{
    return dateTimeString.replace(' ', 'T') + "+00:00";
}

angular.module('animeStocks', ['ngRoute', 'slugifier', 'angular-google-analytics'])

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

        var getStats = function()
        {
            return $http.get('/api/stats').then(function (res) {
                return res.data;
            });
        };

        return {
            getAnimeList: getAnimeList,
            getHistoryForAnime: getHistoryForAnime,
            getAnime: getAnime,
            getStats: getStats
        }
    })

    .controller('SidebarController', function($scope, $rootScope, backendService) {
        $rootScope.loading = true;
        $scope.archiveView = false;

        $scope.current = [];
        $scope.archived = [];

        backendService.getAnimeList().then(function (data) {
            data.forEach(function(animeItem) {
                if (animeItem.archived) $scope.archived.push(animeItem);
                else $scope.current.push(animeItem);
            });
            $rootScope.loading = false;
        });

        $scope.selectAnime = function(anime)
        {
            $rootScope.selectedAnime = anime;
        };

        $scope.checkArchived = function()
        {
            return function(item) {
                if (item.archived === 0 && !$scope.archiveView) return true;
                else return item.archived === 1 && $scope.archiveView;
            }
        };

        $scope.deselectAnime = function()
        {
            $rootScope.selectedAnime = null;
        };

        $scope.clearSearch = function()
        {
            $scope.searchString = '';
        }
    })

    .controller('HomeController', function($scope, backendService) {
        backendService.getStats().then(function (stats) {
            $scope.stats = stats;
        })
    })

    .controller('ExportController', function() { })

    .controller('AnimeController', function($scope, $rootScope, $route, thousandSuffixFilter, backendService) {
        var ratingData = [];
        var membersData = [];
        var episodePlotLines = [];
        $scope.loading = true;

        $scope.loadHistory = function()
        {
            if ($scope.loading === false) $scope.loading = true;
            console.log('Loading history...');
            backendService.getHistoryForAnime($route.current.params['animeId']).then(function (data) {
                console.log('Fetched ' + data.length + ' snapshots from API. Parsing...');
                data.forEach(function(snapshot) {
                    ratingData.push([Date.parse(mysqlToIsoDateTime(snapshot.created_at)), snapshot.rating]);
                    membersData.push([Date.parse(mysqlToIsoDateTime(snapshot.created_at)), snapshot.members]);
                });

                console.log('Parsing complete. Drawing plotlines...');

                var weekInMilliseconds = 7 * 24 * 60 * 60 * 1000;
                for(var i = $rootScope.selectedAnime.start * 1000; i < Date.now() + weekInMilliseconds * 3; i += weekInMilliseconds)
                {
                    episodePlotLines.push({
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

                var darkTheme = {
                    colors: ['#DDDF0D', '#55BF3B', '#DF5353', '#7798BF', '#aaeeee', '#ff0066', '#eeaaee',
                        '#55BF3B', '#DF5353', '#7798BF', '#aaeeee'],
                    chart: {
                        backgroundColor: '#222222',
                        // borderWidth: 2,
                        className: 'dark-container',
                        plotBackgroundColor: '#222222',
                        plotBorderColor: '#333333',
                        plotBorderWidth: 1
                    },
                    title: {
                        style: {
                            color: '#C0C0C0',
                            font: 'bold 16px "Trebuchet MS", Verdana, sans-serif'
                        }
                    },
                    subtitle: {
                        style: {
                            color: '#666666',
                            font: 'bold 12px "Trebuchet MS", Verdana, sans-serif'
                        }
                    },
                    xAxis: {
                        gridLineColor: '#333333',
                        gridLineWidth: 1,
                        labels: {
                            style: {
                                color: '#A0A0A0'
                            }
                        },
                        lineColor: '#A0A0A0',
                        tickColor: '#A0A0A0',
                        title: {
                            style: {
                                color: '#CCC',
                                fontWeight: 'bold',
                                fontSize: '12px',
                                fontFamily: 'Trebuchet MS, Verdana, sans-serif'

                            }
                        }
                    },
                    yAxis: {
                        gridLineColor: '#333333',
                        labels: {
                            style: {
                                color: '#A0A0A0'
                            }
                        },
                        lineColor: '#A0A0A0',
                        minorTickInterval: null,
                        tickColor: '#A0A0A0',
                        tickWidth: 1,
                        title: {
                            style: {
                                color: '#CCC',
                                fontWeight: 'bold',
                                fontSize: '12px',
                                fontFamily: 'Trebuchet MS, Verdana, sans-serif'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.75)',
                        style: {
                            color: '#F0F0F0'
                        }
                    },
                    toolbar: {
                        itemStyle: {
                            color: 'silver'
                        }
                    },
                    plotOptions: {
                        line: {
                            dataLabels: {
                                color: '#CCC'
                            },
                            marker: {
                                lineColor: '#333'
                            }
                        },
                        spline: {
                            marker: {
                                lineColor: '#333'
                            }
                        },
                        scatter: {
                            marker: {
                                lineColor: '#333'
                            }
                        },
                        candlestick: {
                            lineColor: 'white'
                        }
                    },
                    legend: {
                        itemStyle: {
                            font: '9pt Trebuchet MS, Verdana, sans-serif',
                            color: '#A0A0A0'
                        },
                        itemHoverStyle: {
                            color: '#FFF'
                        },
                        itemHiddenStyle: {
                            color: '#444'
                        }
                    },
                    credits: {
                        style: {
                            color: '#666'
                        }
                    },
                    labels: {
                        style: {
                            color: '#CCC'
                        }
                    },

                    navigation: {
                        buttonOptions: {
                            symbolStroke: '#DDDDDD',
                            hoverSymbolStroke: '#FFFFFF',
                            theme: {
                                fill: '#222222',
                                stroke: '#000000'
                            }
                        }
                    },

                    // scroll charts
                    rangeSelector: {
                        buttonTheme: {
                            fill: '#222222',
                            stroke: '#000000',
                            style: {
                                color: '#CCC',
                                fontWeight: 'bold'
                            },
                            states: {
                                hover: {
                                    fill: '#222222',
                                    stroke: '#000000',
                                    style: {
                                        color: 'white'
                                    }
                                },
                                select: {
                                    fill: '#222222',
                                    stroke: '#000000',
                                    style: {
                                        color: 'yellow'
                                    }
                                }
                            }
                        },
                        inputStyle: {
                            backgroundColor: '#333',
                            color: 'silver'
                        },
                        labelStyle: {
                            color: 'silver'
                        }
                    },

                    navigator: {
                        handles: {
                            backgroundColor: '#666',
                            borderColor: '#AAA'
                        },
                        outlineColor: '#CCC',
                        maskFill: 'rgba(16, 16, 16, 0.5)',
                        series: {
                            color: '#7798BF',
                            lineColor: '#A6C7ED'
                        }
                    },

                    scrollbar: {
                        barBackgroundColor: '#222222',
                        barBorderColor: '#CCC',
                        buttonArrowColor: '#CCC',
                        buttonBackgroundColor: '#222222',
                        buttonBorderColor: '#CCC',
                        rifleColor: '#FFF',
                        trackBackgroundColor: '#222222',
                        trackBorderColor: '#666'
                    },

                    // special colors for some of the
                    legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
                    background2: 'rgb(35, 35, 70)',
                    dataLabelsColor: '#444',
                    textColor: '#C0C0C0',
                    maskColor: 'rgba(255,255,255,0.3)'
                };

                // Highcharts.setOptions(darkTheme);

                console.log('Loading chart data complete. Creating chart...');
                Highcharts.stockChart('chart-container', {
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
                        panning: true,
                        type: 'line'
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
                        ]],
                        plotLines: episodePlotLines,
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
                        softMax: 10,
                        opposite: false
                    }, {
                        id: 'members-axis',
                        title: {
                            text: 'Members (popularity)'
                        },
                        min: 0
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
                            ]],
                            labels: {
                                format: '{value:%b %e}'
                            }
                        }
                    },

                    series: [{
                        id: 'rating-series',
                        name: 'Average Rating',
                        yAxis: 0,
                        data: ratingData,
                        tooltip: {
                            headerFormat: '{point.x:%b %e, %H:%M (UTC)}<br>',
                            pointFormat: 'Average rating: <b>{point.y:.2f}</b><br>',
                            crosshairs: [true],
                            padding: 50
                        },
                        plotOptions: {
                        }
                    }, {
                        id: 'members-series',
                        name: 'Members (popularity)',
                        yAxis: 1,
                        data: membersData,
                        tooltip: {
                            pointFormat: 'Members: <b>{point.y:,.0f}</b>',
                            padding: 50
                        }
                    }],

                    colors: ['#2196F3', '#FFC107', '#90ed7d', '#f7a35c', '#8085e9',
                        '#f15c80', '#e4d354', '#8085e8', '#8d4653', '#91e8e1']
                });

                console.log('Chart created successfully.');
                $scope.loading = false;
            });
        };

        if (!$rootScope.selectedAnime)
        {
            console.log('Fetching anime ID ' + $route.current.params['animeId'] + ' from API...');
            backendService.getAnime($route.current.params['animeId']).then(function (data) {
                $rootScope.selectedAnime = data;
                console.log('Loaded anime from API: ' + $rootScope.selectedAnime.title);
                $rootScope.title = $rootScope.selectedAnime.title;
                $scope.loadHistory();
            });
        }
        else
        {
            console.log('Loaded anime from memory: ' + $rootScope.selectedAnime.title);
            $rootScope.title = $rootScope.selectedAnime.title;
            $scope.loadHistory();
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

            .when('/database', {
                title: 'Database Dumps',
                templateUrl: 'export.html',
                controller: 'ExportController'
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

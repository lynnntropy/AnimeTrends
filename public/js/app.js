angular.module('animeStocks', ['ngRoute', 'chart.js'])

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

        return {
            getAnimeList: getAnimeList,
            getHistoryForAnime: getHistoryForAnime
        }
    })

    .controller('SidebarController', function($scope, backendService) {
        backendService.getAnimeList().then(function (data) {
            console.log(data);
            $scope.anime = data;
        });
    })

    .controller('HomeController', function() {})

    .controller('AnimeController', function($scope, $route, backendService) {
        $scope.data = [[], []];
        $scope.labels = [];
        $scope.series = ['Avg. Rating', 'Members'];
        $scope.datasetOverride = [{ yAxisID: 'y-axis-1' }, { yAxisID: 'y-axis-2' }];
        $scope.options = {
            scales: {
                yAxes: [
                    {
                        id: 'y-axis-1',
                        type: 'linear',
                        display: true,
                        position: 'left',
                        gridLines: {
                            display:false
                        },
                        ticks: {
                            min: 1,
                            max: 10
                        }
                    },
                    {
                        id: 'y-axis-2',
                        type: 'linear',
                        display: true,
                        position: 'right',
                        gridLines: {
                            display:false
                        }
                    }
                ]
            }
        };

        backendService.getHistoryForAnime($route.current.params['animeId']).then(function (data) {
            console.log(data);
            data.forEach(function(snapshot) {
                $scope.data[0].push(snapshot.rating);
                $scope.data[1].push(snapshot.members);
                $scope.labels.push(snapshot.created_at);
            });
        })
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

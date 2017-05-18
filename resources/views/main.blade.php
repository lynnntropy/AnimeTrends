<html ng-app="animeStocks">
<head>
    <meta charset="UTF-8">
    @if(isset($anime))
        <meta property="og:title" content="AnimeTrends - {{$anime->title}}" />
        <meta property="og:description" content="Rating and popularity history for {{$anime->title}} on MyAnimeList." />
        <meta property="og:image" content="{{$anime->image}}" />
    @else
        <meta property="og:title" content="AnimeTrends" />
        <meta property="og:description" content="A site that tracks rating and popularity history for anime on MyAnimeList." />
    @endif
    <meta property="og:site_name" content="AnimeTrends" />
    <base href="/">

    <title ng-bind="'AnimeTrends - ' + title">AnimeTrends</title>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <link href="{{asset('node_modules/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('node_modules/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{asset('css/app.css')}}" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-2 sidebar-column" ng-controller="SidebarController">
            <div class="sidebar" ng-cloak>
                <div class="sidebar-header">
                    <a href="/" ng-click="deselectAnime()"><img class="logo-image" src="/images/logo-white-small.png"></a>
                </div>
                <div class="search-container">
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control has-feedback anime-search" placeholder="Search..." ng-model="searchString">
                        <span ng-if="searchString" ng-click="clearSearch()" class="form-control-feedback" uib-tooltip="clear">&times;</span>
                    </div>
                </div>
                <div class="list-tabs">
                    <div class="tab" ng-class="{'active': !archiveView}" ng-click="archiveView = false">Current <span class="count">(@{{current.length}})</span></div>
                    <div class="tab" ng-class="{'active': archiveView}" ng-click="archiveView = true">Archive <span class="count">(@{{archived.length}})</span></div>
                </div>
                <div class="list-container" ng-show="!loading && !archiveView">
                    <a href="/anime/@{{item.id}}/@{{item.title | slugify}}" ng-repeat="item in current | filter:searchString" ng-click="selectAnime(item)">
                        <div class="anime-item" ng-class="{'selected': (selectedAnime.id == item.id)}">
                            <p class="item-title">@{{item.title}}</p>
                            <p class="item-details-line">@{{item.members | thousandSuffix:1}} members, <span ng-style="getRatingStyle(item.rating)">@{{item.rating}}</span> average</p>
                        </div>
                    </a>
                </div>
                <div class="list-container" ng-show="!loading && archiveView">
                    <a href="/anime/@{{item.id}}/@{{item.title | slugify}}" ng-repeat="item in archived | filter:searchString" ng-click="selectAnime(item)">
                        <div class="anime-item" ng-class="{'selected': (selectedAnime.id == item.id)}">
                            <p class="item-title">@{{item.title}}</p>
                            <p class="item-details-line">@{{item.members | thousandSuffix:1}} members</p>
                        </div>
                    </a>
                </div>
                <div class="sidebar-loader-container" ng-if="loading">
                    <div class="sidebar-loader"></div>
                </div>
            </div>
        </div>
        <div class="col-xs-10 main">
            <div ng-view></div>
        </div>
    </div>
</div>

<script src="{{asset('node_modules/jquery/dist/jquery.min.js')}}"></script>
<script src="{{asset('node_modules/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script src="{{asset('node_modules/angular/angular.min.js')}}"></script>
<script src="{{asset('node_modules/angular-route/angular-route.min.js')}}"></script>
<script src="{{asset('node_modules/highcharts/highstock.js')}}"></script>
<script src="{{asset('node_modules/highcharts-ng/dist/highcharts-ng.min.js')}}"></script>
<script src="{{asset('js/angular-ga.js')}}"></script>
<script src="{{asset('js/angular-slugify.js')}}"></script>
<script src="{{asset('js/app.js')}}"></script>
</body>
</html>
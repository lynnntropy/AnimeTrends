<html ng-app="animeStocks">
<head>
    <meta charset="UTF-8">
    @if(isset($anime))
        <meta property="og:title" content="Anime Stocks - {{$anime->title}}" />
        <meta property="og:description" content="Rating and popularity history for {{$anime->title}} on MyAnimeList." />
        <meta property="og:image" content="{{$anime->image}}" />
    @else
        <meta property="og:title" content="Anime Stocks" />
        <meta property="og:description" content="A site that tracks rating and popularity history for anime on MyAnimeList." />
    @endif
    <meta property="og:site_name" content="Anime Stocks" />
    <base href="/">

    <title ng-bind="'Anime Stocks - ' + title">Anime Stocks</title>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <link href="{{asset('node_modules/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('css/app.css')}}" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-2 sidebar-column" ng-controller="SidebarController">
            <div class="sidebar" ng-cloak>
                <input type="text" class="form-control anime-search" placeholder="Search..." ng-model="searchString">
                <div class="list-tabs">
                    <div class="tab" ng-class="{'active': !archiveView}" ng-click="archiveView = false">Current</div>
                    <div class="tab" ng-class="{'active': archiveView}" ng-click="archiveView = true">Archive</div>
                </div>
                <div class="list-container">
                    <a href="/anime/@{{item.id}}/@{{item.title | slugify}}" ng-repeat="item in anime | filter:searchString" ng-click="selectAnime(item)">
                        <div class="anime-item" ng-class="{'selected': (selectedAnime.id == item.id)}">
                            <p class="item-title">@{{item.title}}</p>
                            <p class="item-members">Members: @{{item.members | thousandSuffix:1}}</p>
                        </div>
                    </a>
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
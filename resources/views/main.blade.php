<html ng-app="animeStocks">
<head>
    <meta charset="UTF-8">
    <base href="/">

    <title>Anime Stocks</title>
    <link href="{{asset('node_modules/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('css/app.css')}}" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-2 sidebar" ng-controller="SidebarController">
            <a href="/anime/@{{item.id}}/@{{item.title | slugify}}" ng-repeat="item in anime" ng-click="selectAnime(item)">
                <div class="anime-item">
                    @{{item.title}}
                </div>
            </a>
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
{{--<script src="{{asset('node_modules/highcharts/highcharts.js')}}"></script>--}}
<script src="{{asset('node_modules/highcharts/highstock.js')}}"></script>
<script src="{{asset('node_modules/highcharts-ng/dist/highcharts-ng.min.js')}}"></script>
<script src="{{asset('js/angular-slugify.js')}}"></script>
<script src="{{asset('js/app.js')}}"></script>
</body>
</html>
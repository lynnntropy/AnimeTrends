# AnimeTrends (Backend)

AnimeTrends is a website and open-source project with the mission of tracking the ratings and popularity of titles on MyAnimeList over time, and making this data 
freely available to anyone who wants it.

This repository contains the backend application for AnimeTrends, written in PHP. 

## Getting Started

Before you start, rename `.env.example` to `.env` and update it to fit your environment.

After you've done that, you can proceed to install the dependencies and start the local 
development server.

```bash
composer install
php artisan key:generate
php artisan serve
```

### Prerequisites

- [Composer](https://getcomposer.org/)

## Built With

* PHP
* [Laravel](https://laravel.com/) - A modern PHP framework
* [Composer](https://getcomposer.org/) - Dependency manager for PHP

## Authors

* **Veselin Romić (OmegaVesko)**

## License

This project is licensed under the GPL v3 - see the [LICENSE](LICENSE) file for details.
# shoppingfeed/signal

Unix signal handler that allow registering multiples callback on the same signal

### Installation

```
composer require shoppingfeed/signal
```

### Contributing

To connect to a php 8.2 container correctly configured

- Create and start container : `docker run --name module-signal-php -v $PWD:/var/www -d ghcr.io/shoppingflux/php:8.2-unit`
- Start existing container : `docker start module-signal-php`
- Connect to container : `docker exec -it module-signal-php bash`

Once connected to the container you can :

- Update composer dependencies : `composer update`
- Run test : `composer test`

### Documentation

Documentation is driven by [mkdocs](https://www.mkdocs.org/) and uses [material theme](https://squidfunk.github.io/mkdocs-material/)

- Find it at [docs/index.md](docs/index.md)
- Or Run the doc server locally :

```
docker run --rm -it -p 8000:8000 -v ${PWD}:/docs squidfunk/mkdocs-material
```

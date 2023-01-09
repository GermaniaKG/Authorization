# Germania KG · Authorization

**Simple authorization solution with [PSR-11 Container](https://github.com/php-fig/container) compatibility and PSR-7 style Middleware. No hierarchical stuff so far.**

[![Packagist](https://img.shields.io/packagist/v/germania-kg/authorization.svg?style=flat)](https://packagist.org/packages/germania-kg/authorization)
[![PHP version](https://img.shields.io/packagist/php-v/germania-kg/authorization.svg)](https://packagist.org/packages/germania-kg/authorization)
[![Tests](https://github.com/GermaniaKG/Authorization/actions/workflows/tests.yml/badge.svg)](https://github.com/GermaniaKG/Authorization/actions/workflows/tests.yml)


## Installation

```bash
$ composer require germania-kg/authorization
```


## Setup

The *Authorization* constructor requires an Access Control List, i.e. an array with *tasks* as keys and *allowed roles arrays* as elements. The second parameter defines whether to permit in case a task is not defined.

```php
<?php
use Germania\Authorization\Authorization;

// Define tasks and allowed roles
$acl = array(
	'/foo' => [ "coworkers", "superuser"],
	'/bar' => [ "superuser", "registered"]
);

// Wether to permit undefined tasks
$default_permission = true;

// Create instance, optional with PSR-3 Logger
$authorization = new Authorization( $acl, $default_permission );
$authorization = new Authorization( $acl, $default_permission, $logger );
```

## Usage
The *Authorization* class implements the *AuthorizationInterface* which defines a single *authorize* method. Additionally, *Authorization* provides a *__invoke* function und thus is callable.

```php
<?php
$user_roles = [ "coworkers", "somegroup" ];

// Result is TRUE
$allowed = $authorization->authorize("/foo", $user_roles);
$allowed = $authorization("/foo", $user_roles);

// Result is FALSE
$allowed = $authorization->authorize("/bar", $user_roles);
$allowed = $authorization("/bar", $user_roles);

// Should be TRUE due to default permission above
$allowed = $authorization->authorize("/somethingelse", $user_roles);
$allowed = $authorization("/somethingelse", $user_roles);
```

**Per-task logging:** Both *authorize* and *__invoke* Methods do accept an optional PSR-3 Logger instance. This enables you to disable or override the default logger you passed on instantiation. Example:

```php
<?php
$silent_log = new Psr\Log\NullLogger;

$authorization->authorize("/foo", $user_roles, $silent_log);
$authorization("/foo", $user_roles, $silent_log);
```

## Container Interoperability

The *AuthorizationInterface* implements both [PSR-11 ContainerInterface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md) and the deprecated *[Interop\Container\ContainerInterface](https://github.com/container-interop/container-interop/blob/master/docs/ContainerInterface.md)* for backward compatibility.
So you can test if your *Authorization* instance *has* a task and *get* the allowed roles.

If a task is not defined, a *TaskNotFoundException* exception will be thrown. This class implements both the *[Interop\Container\Exception\NotFoundException](https://github.com/container-interop/container-interop/blob/master/docs/ContainerInterface.md#4-interopcontainerexceptioncontainerexception)* and PSR-11's [Psr\Container\NotFoundExceptionInterface](https://github.com/php-fig/container/blob/master/src/NotFoundExceptionInterface.php) interface.

More information: [PSR-11 Container](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md) • [container-interop/container-interop](https://github.com/container-interop/container-interop)


```php
<?php
use Germania\Authorization\TaskNotFoundException;
use Psr\Container\NotFoundExceptionInterface;

// Assuming example from above:
// TRUE
$has = $authorization->has( "/foo" );

// array( "coworkers", "superuser"] )
try {
	$roles = $authorization->get( "/foo" );
	
	// will throw TaskNotFoundException
	$roles = $authorization->get( "/something-else" );
}
catch (NotFoundExceptionInterface $e) {
	if ($e instanceOf NotFoundException) {
		echo "Interop Container: NotFoundException";
	}
}
```

## PSR 7-style Middleware

This packages offers three PSR7-style middlewares. All take a *Callable* authorizer (e.g. class Authorization, see above) and optionally a PSR-3 Logger.

If authorization fails, the Response object gets a `401 Unauthorized` status; after that, the next middelware will be called. This enables you to work with unauthorized requests in later middlewares or controllers.—Well, this is what basically happens inside:

```php
// Your Callable passed into constructor
$authorize = $this->authorizer;

if (!$authorize( $url )):
	$response = $response->withStatus( 401 );
endif;

$response = $next($request, $response);
return $response;
```


### Request URI Authorization
**RequestUriAuthorizationMiddleware** will check [PSR-7 Request's](http://www.php-fig.org/psr/psr-7/#3-2-psr-http-message-requestinterface) URI string; suitable in most cases.

```php
<?php
use Germania\Authorization\RequestUriAuthorizationMiddleware;

// Have your Authorization callable at hand
$auth = new Authorization( ... );

// Optionally with PSR-3 Logger
$middleware = new RequestUriAuthorizationMiddleware( $auth )
$middleware = new RequestUriAuthorizationMiddleware( $auth, $logger )
```



### Route Name Authorization
**RouteNameAuthorizationMiddleware** is for those working with [Slim Framework's Route Names](http://www.slimframework.com/docs/objects/router.html#route-names). To get access to current route name, set *determineRouteBeforeAppMiddleware* in Slim's configuration settings to *true*.


```php
<?php
use Germania\Authorization\RouteNameAuthorizationMiddleware;

// Have your Authorization callable at hand
$auth = new Authorization( ... );

// Optionally with PSR-3 Logger
$middleware = new RouteNameAuthorizationMiddleware( $auth );
$middleware = new RouteNameAuthorizationMiddleware( $auth, $logger );

// Setup Slim App:
$app = new \Slim\App( [
	'settings' => [
		// Set this to true to get access to route within middleware
		'determineRouteBeforeAppMiddleware' => true
	]
]);

// Add Middleware
$app->add( $middleware );
```




### Customizable Authorization
**AuthorizationMiddleware** is the base class of the two above, and more configurable. It takes *another Callable* returning a custom term (or “permission”, you name it) you like to authorize, next to our Authorization *Callable* from the examples above.



```php
<?php
use Germania\Authorization\AuthorizationMiddleware;

// Have your Authorization callable at hand
$auth = new Authorization( ... );

// Setup Callable for URLs (or, permissions, you name it)
$url_getter = function( $request ) {
	return (string) $request->getUri();
};

// Optionally with PSR-3 Logger
$middleware = new AuthorizationMiddleware( $auth, $url_getter );
$middleware = new AuthorizationMiddleware( $auth, $url_getter, $logger );
```

## Issues

See [issues list.][i0]

[i0]: https://github.com/GermaniaKG/Authorization/issues

## Development

```bash
$ git clone https://github.com/GermaniaKG/Authorization.git
$ cd Authorization
$ composer install
```

## Unit tests

Either copy `phpunit.xml.dist` to `phpunit.xml` and adapt to your needs, or leave as is. Run [PhpUnit](https://phpunit.de/) test or composer scripts like this:

```bash
$ composer test
# or
$ vendor/bin/phpunit
```

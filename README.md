#Authorization

**Simple authorization solution with no hierarchical stuff so far.**

##Installation

```bash
$ composer require germania-kg/authorization
```


##Setup

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

// Create instance, optional woth PSR-3 Logger
$authorization = new Authorization( $acl, $default_permission );
$authorization = new Authorization( $acl, $default_permission, $logger );
```

##Usage
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


##Development and testing

Clone repo, use [Git Flow](https://github.com/nvie/gitflow). Work on *develop* branch.

```bash
# Clone Repo
$ git clone git@github.com:GermaniaKG/Authorization.git germania-authorization
$ cd germania-authorization
$ composer install
```

For testing, copy PHPUnit configuration file and customize if needed.

```bash
$ cp phpunit.xml.dist phpunit.xml
$ phpunit
```

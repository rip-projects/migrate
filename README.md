migrate
=======

Story of product developers:

"I'm glad that our application is getting traction more and more each day. There are another demands to fullfill.
Our customers asking about this module.. that module.."

"Yeah, I feel it too boss, and it seems that we need to refactor our codes and changed the data infrastructure every
release of versions"

"So how do we do that??"

# Introduction

Thats what we feel too. We need some scripting to do migration between version. And for us (Bono Developers), we present
you "migrate".

# Installation

"migrate" will run as Provider. You can put configuration to your config file to enable migration schema on your application.

```php
return array(
    'bono.providers' => array(
        '\\Xinix\\Migrate\\Provider\\MigrateProvider' => array(
            'token' => 'token-to-access-from-web',
        ),
    ),
);
```

"That's it??"

Yep! you can open the web console from url http://your-app/index.php/migrate?token=token-to-access-from-web

# CLI

```
$ xpax task
Available tasks:
  init
  serve
  migrate:generate
  migrate
  migrate:run
  migrate:rollback
.OK
```

## Generate new version

```
xpax migrate:generate [version label]
```

## Run migration

```
xpax migrate
```

or 

```
xpax migrate:run
```

## Rollback migration

```
xpax migrate:rollback
```

.



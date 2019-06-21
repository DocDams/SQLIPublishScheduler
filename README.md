SQLI Publish Scheduler
========================================

[SQLI](http://www.sqli.com) Publish Schedule is a bundle to perform a delayed publish and unpublish for content

By default, publish/unpublish works with Location's visibility 
(variable `sqli_publish_scheduler.publish_scheduler_handler`) and dates must be defined in 2 ContentFields 
(eZDate or eZDateTime)

Installation
------------

### Install with composer
```
composer require sqli/publish_scheduler:dev-master
```


### Register the bundle

Activate the bundle in `app/AppKernel.php`

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new SQLI\PublishSchedulerBundle\SQLIPublishSchedulerBundle(),
    ];
}
```

### ContentType

Add 2 new fields (ezdate or ezdatetime) for each ContentType concerned by delayed publication.
Expected identifiers : `publish_date` and `unpublish_date`, or whatever defined in 
`sqli_publish_scheduler.ezdatetime_field_publish` and `sqli_publish_scheduler.ezdatetime_field_unpublish`



### Parameters (optional)

This is the defaults parameters

```yml
sqli_publish_scheduler:
    ezdatetime_field_publish: 'publish_date'
    ezdatetime_field_unpublish: 'unpublish_date'
    publish_scheduler_handler: '@sqli_publish_scheduler.handler.visibility'
```


### Cron (optional)

Command cronjob used ezplatform-cron and already declared in `services.yml` to publish/unpublish contents.
You can override this service in order to change frequency (every minute by default)

```yml
services:
    sqli_publish_scheduler.cron:
        class: SQLI\PublishSchedulerBundle\Command\PublishUnpublishCommand
        tags:
            - { name: console.command }
            - { name: ezplatform.cron.job, schedule: '* * * * *' }
```

You can change the frequency of the cronjob with `schedule` property according to 
[CRON expression](https://en.wikipedia.org/wiki/Cron#CRON_expression)

Please note that no category is defined, so this cronjob is in category `default`

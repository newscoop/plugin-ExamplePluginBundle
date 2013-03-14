ExamplePluginBundle
===================

Newscoop ExamplePluginBundle

## Instalation/Updating/Removing
#### Overview

Whole plugins system (instalation/managemenet) is based on [Composer][1] packages.
Packages can live on [github.com][github] or private own git repositories but they must be listed on [packagist.org][packagist] or private own (based on [satis][satis]) composer repositories.

For now we support only this way of plugins management. But we have in plans instalations from .zip files.

Whole management process should be dony by our Newscoop\Services\PluginsManagerService class. It's important because this way allow for developers to react on instalation/remove/update events (and more) in their plugins.

#### Instalation

```
    php application/console plugins:install "vendor\plugin-name" "optional version"
```
Install command will add your package to your composer.json file (and install it) and update plugins/avaiable_plugins.json file (used for plugin booted as Bundle). This command will also fire "plugin.install" event with plugin_name parameter in event data

#### Removing

```
    php application/console plugins:remove "vendor\plugin-name"
```
Remove command will remove your package from composer.sjon file and update your dependencies (for now this is only way), it will also remove info about plugin from plugins/avaiable_plugins.json file and fire "plugin.remove" event with plugin_name parameter in event data.

#### Updating

```
    php application/console plugins:update "vendor\plugin-name" "optional version"
```

Update command is little specific - it will first remove your your plugin form newscoop (but won't fire plugin.remove event) and after that will install again your plugin (again without plugin.install event). After all of that it will fire plugin.update event.

## Manage plugin lifecycle

The best way for plugin lifecycle management is registering event subscriber. Events lifecycle consists of 3 events:

    - plugin.install
    - plugin.remove
    - plugin update

This is example of simple event subscriber class:

```
// ExamplePluginBundle/EventListener/LifecycleSubscriber.php
<?php
namespace Newscoop\ExamplePluginBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;

/**
 * Event lifecycle management
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    public function install(GenericEvent $event)
    {
        // do something on install
    }

    public function update(GenericEvent $event)
    {
        // do something on update
    }

    public function remove(GenericEvent $event)
    {
        // do something on remove
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install' => array('install', 1),
            'plugin.update' => array('update', 1),
            'plugin.remove' => array('remove', 1),
        );
    }
}
```

Next step is registering this class in Event Dispatcher:

```
// ExamplePluginBundle/Resources/config/services.yml
services:
    newscoop_example_plugin.lifecyclesubscriber:
        class: Newscoop\ExamplePluginBundle\EventListener\LifecycleSubscriber
        tags:
            - { name: kernel.event_subscriber}

```
Subscriber can have access for all registered in container services (```php application/console container:debug```), only thing what you must to is passing services as argument:

```
// ExamplePluginBundle/Resources/config/services.yml
services:
    newscoop_example_plugin.lifecyclesubscriber:
        class: Newscoop\ExamplePluginBundle\EventListener\LifecycleSubscriber
        arguments:
            - @em
        tags:
            - { name: kernel.event_subscriber}

```
and using it in your service (subscriber):

```
// ExamplePluginBundle/EventListener/LifecycleSubscriber.php
...
class LifecycleSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct($em) {
        $this->em = $em;
    }
    ...

```
In subscriber included in this plugin you can find exaple of database updating (based on doctrine entities and schema tool)

## Create new controllers (routings)

## Create plugin entities

## Provide smarty blocks

//smarty plugins directory in plugin Resources directory.

## Provide newscoop widgets

//newscoopWidgets directory in plugin root directory.

## Register events listeners

[1]: http://getcomposer.org/doc/00-intro.md
[packagist]: https://packagist.org/
[github]: https://github.com/
[satis]: https://github.com/composer/satis
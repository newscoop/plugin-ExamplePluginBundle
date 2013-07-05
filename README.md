ExamplePluginBundle
===================

Newscoop ExamplePluginBundle

## Installation/Updating/Removing
#### Overview

The whole plugin system (installation/management) is based on [Composer][1] packages.
Packages can live on [github.com][github] or your own private git repositories but they must be listed on [packagist.org][packagist] or private own (based on [satis][satis]) composer repositories.

For now we support only this way of plugins management. But we have plans for installation from .zip files.

The whole management process should be done through our Newscoop\Services\PluginsManagerService class. It's important because this way we allow for developers to react on installation/remove/update events (and more) in their plugins.

#### Installation

```
    php application/console plugins:install "vendor/plugin-name" "optional version"
    php application/console plugins:install "newscoop/example-plugin-bundle" --env=prod # installs this plugin
```
Install command will add your package to your composer.json file (and install it) and update plugins/avaiable_plugins.json file (used for plugin booted as Bundle). This command will also fire "plugin.install" event with plugin_name parameter in event data

#### Removing

```
    php application/console plugins:remove "vendor/plugin-name"
    php application/console plugins:remove "newscoop/example-plugin-bundle" --env=prod # removes this plugin
```
Remove command will remove your package from composer.json file and update your dependencies (for now this is only way), it will also remove info about plugin from plugins/avaiable_plugins.json file and fire "plugin.remove" event with plugin_name parameter in event data.

#### Updating

```
    php application/console plugins:update "vendor/plugin-name" "optional version"
    php application/console plugins:update "newscoop/example-plugin-bundle" --env=prod # updates this plugin
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

Newscoop plugins system is based on the Symfony Bundles system - so (almost) all Symfony features are avaiable.
If you want create own new controller (with routing) then you must only do a few things, first - create controller class:

```
// ExamplePluginBundle/Controller/LifecycleSubscriber.php
<?php

namespace Newscoop\ExamplePluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/testnewscoop")
     */
    public function indexAction(Request $request)
    {
        return $this->render('NewscoopExamplePluginBundle:Default:index.html.smarty');
    }
}
``` 

In this example we will use annotations for configuration (```@Route("/testnewscoop")```).
Second we must register our Controller class (routing) in system:

```
// ExamplePluginBundle/Resources/config/routing.yml
newscoop_example_plugin:
    resource: "@NewscoopExamplePluginBundle/Controller/"
    type:     annotation
    prefix:   /
```
With that configuration in routing.yml Newscoop will be informed about all plugin controllers.

#### Work with views (templates)
As you can see we can return smarty view from controler (as response): 

```return $this->render('NewscoopExamplePluginBundle:Default:index.html.smarty');```

of course we can pass data from controller to our view:

```
return $this->render('NewscoopExamplePluginBundle:Default:index.html.smarty', array(
    'variable' => 'super extra variable'
));
```

lets go to template:

```
// ExamplePluginBundle/Resources/views/Default/index.html.smarty
    <h1>this is my variable {{ $variable }} !</h1>
```
This is very simple template, but we can change this - lets extend our Newscoop default publication theme layout:

This is our default theme page.tpl file 

```
// ex. newscoop/themes/publication_1/theme_1/page.tpl
    {{ include file="_tpl/_html-head.tpl" }}
    <div id="wrapper">

    {{ include file="_tpl/header.tpl" }}
    <div id="content" class="clearfix">
        <section class="main entry page">
            {{ block content }}{{ /block }}
        </section>
        ...
```
And in our plugin template we can do something like this:

```
{{extends file="page.tpl"}}
{{block content}}
    <h1>this is my variable {{ $variable }} !</h1>
{{/block}}
```


## Create plugin entities

In plugins and in Newscoop we use Doctrine2 for database entities management, so if you know Doctrine2 then you will know how to work with entities in plugins. If you don't know about Doctrine2 then this is great oportunity - [read more here][doctrine]

Few important things:

* You can get entity manager from newscoop container (in plugin controlle simply use ```$this->container->get('em');```)
* We use full FQN notation ex. ```$em->getRepository('Newscoop\ExamplePluginBundle\Entity\OurEntity');``` 

## Provide admin controllers

All we need is a simple controller (ex. ```Newscoop\ExamplePluginBundle\Controller\DefaultController```) with action and routing.
In plugins admin controllers we can use twig (also smarty) as template engine. You can see how extend default admin layout (header + menu+footer) here: ```Resources/views/Default/admin.html.twig```

### Provide plugin menu in Newscoop admin menu:

For Newscoop menu we use [KNP/Menu][KNP/Menu] library (with [KNP/MenuBundle][KNP/MenuBundle]), so extending o=newscoop menu is realy easy.

We need only one service (and declaration in services) - declaration:

```
    newscoop_example_plugin.configure_menu_listener:
        class: Newscoop\ExamplePluginBundle\EventListener\ConfigureMenuListener
        tags:
          - { name: kernel.event_listener, event: newscoop_newscoop.menu_configure, method: onMenuConfigure }
```

and menu configuration litener 

```
// EventListener/ConfigureMenuListener.php
<?php
namespace Newscoop\ExamplePluginBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu[getGS('Plugins')]->addChild(
            'Example Plugin', 
            array('uri' => $event->getRouter()->generate('newscoop_exampleplugin_default_admin'))
        );
    }
}
```

and it's it - you should have your plugin menu in newscoop menu.

## Provide smarty plugins

As main template language we use smarty3. Smarty have realy nice feature called "Plugins" - read more about them [here][smarty].

If you want provide smarty plugins with your Newscoop plugin you must keep them in specific place:

``` ex. ExamplePluginBundle/Resources/smartyPlugins ```

Thats all - plugins will be autloaded and avaiable in your templates.

## Provide newscoop widgets

Newscoop admin panel have realy cool feature - dashboard widgets. Read more about Newscoop Dashboard Widgets [here][dashboard-widgets]

If you want enable your widget you must place it in special distecotry inside plugin:

``` ExamplePluginBundle/newscoopWidgets ```

``` // for example:  ExamplePluginBundle/newscoopWidgets/mysuperwidget ```

## Register events listeners

[1]: http://getcomposer.org/doc/00-intro.md
[packagist]: https://packagist.org/
[github]: https://github.com/
[satis]: https://github.com/composer/satis
[doctrine]: http://docs.doctrine-project.org/en/latest/
[smarty]: http://www.smarty.net/docs/en/plugins
[dashboard-widgets]: http://www.sourcefabric.org/en/community/blog/1404/How-to-create-dashboard-widgets-in-Newscoop.htm
[KNP/Menu]: https://github.com/KnpLabs/KnpMenu
[KNP/MenuBundle]: https://github.com/KnpLabs/KnpMenuBundle

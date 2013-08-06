<?php

namespace Newscoop\ExamplePluginBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Newscoop\EventDispatcher\Events\PluginHooksEvent;

class HooksListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function sidebar(PluginHooksEvent $event)
    {
        $response = $this->container->get('templating')->renderResponse(
            'NewscoopExamplePluginBundle:Hooks:sidebar.html.twig',
            array(
                'pluginName' => 'ExamplePluginBundle',
                'info' => 'This is response from plugin hook!'
            )
        );

        $event->addHookResponse($response);
    }
}
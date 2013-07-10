<?php

namespace Newscoop\ExamplePluginBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Newscoop\EventDispatcher\Events\PluginHooksEvent;

class HooksController
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function sidebarAction(PluginHooksEvent $event)
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

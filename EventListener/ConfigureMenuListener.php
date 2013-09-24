<?php
/**
 * @package Newscoop\ExamplePluginBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\ExamplePluginBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $translator = $this->container->get('translator');

        $menu[$translator->trans('Plugins')]->addChild(
        	$translator->trans('plugin.admin.titlecontent'), 
        	array('uri' => $event->getRouter()->generate('newscoop_exampleplugin_default_admin'))
        );
    }
}
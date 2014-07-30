<?php
/**
 * @package Newscoop\ExamplePluginBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\ExamplePluginBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;
use Doctrine\ORM\EntityManager;
use Newscoop\Services\SchedulerService;

/**
 * Event lifecycle management
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    protected $em;

    protected $scheduler;

    protected $cronjobs;

    public function __construct(EntityManager $em, SchedulerService $scheduler)
    {
        $appDirectory = realpath(__DIR__.'/../../../../application/console');
        $this->em = $em;
        $this->scheduler = $scheduler;
        $this->cronjobs = array(
            "Example plugin test cron job" => array(
                'command' => $appDirectory . ' example:test',
                'schedule' => '* * * * *',
            ),
            /*"Another test cron job" => array(
                'command' => $appDirectory . ' example:anothertest',
                'schedule' => '* * * * *',
            ),*/
        );
    }

    public function install(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
        $this->addJobs();
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        // Generate proxies for entities
        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
        $this->addJobs();
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);
        $this->removeJobs();
    }

    /**
     * Add plugin cron jobs
     */
    private function addJobs()
    {
        foreach ($this->cronjobs as $jobName => $jobConfig) {
            $this->scheduler->registerJob($jobName, $jobConfig);
        }
    }

    /**
     * Remove plugin cron jobs
     */
    private function removeJobs()
    {
        foreach ($this->cronjobs as $jobName => $jobConfig) {
            $this->scheduler->removeJob($jobName, $jobConfig);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.newscoop_example_plugin' => array('install', 1),
            'plugin.update.newscoop_example_plugin' => array('update', 1),
            'plugin.remove.newscoop_example_plugin' => array('remove', 1),
        );
    }

    private function getClasses()
    {
        return array(
          $this->em->getClassMetadata('Newscoop\ExamplePluginBundle\Entity\Example'),
        );
    }
}

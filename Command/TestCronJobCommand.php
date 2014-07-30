<?php
/**
 * @package   Newscoop\ExamplePluginBundle
 * @author    Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\ExamplePluginBundle\Command;

use Symfony\Component\Console;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Test cron job command
 */
class TestCronJobCommand extends ContainerAwareCommand
{
    /**
     */
    protected function configure()
    {
        $this->setName('example:test')
            ->setDescription('Example test cron job command');
    }

    /**
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        try {
            $output->writeln('<info>Test cron job command.</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Error occured: '.$e->getMessage().'</error>');

            return false;
        }
    }
}

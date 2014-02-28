<?php

namespace Akeneo\WatchExtension\Command;

use PhpSpec\Console\Command\RunCommand as BaseRunCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Lurker\ResourceWatcher;
use Lurker\Event\FilesystemEvent;

class RunCommand extends BaseRunCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption('watch', null, InputOption::VALUE_NONE, 'Rerun specs automatically on file changes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('watch'))
        {
            return parent::execute($input, $output);
        }

        $watcher = new ResourceWatcher;
        $watcher->track('phpspec.specifications', 'spec/');

        $watcher->addListener('phpspec.specifications', function (FilesystemEvent $event) use ($input, $output) {
            $container = $this->getApplication()->getContainer();
            $container->setParam('formatter.name',
                $input->getOption('format') ?: $container->getParam('formatter.name')
            );
            $container->configure();

            $suite       = $container->get('loader.resource_loader')->load($event->getResource());
            $suiteRunner = $container->get('runner.suite');

            return $suiteRunner->run($suite);
        });

        $watcher->start();
    }
}

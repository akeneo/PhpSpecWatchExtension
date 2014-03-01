<?php

namespace Akeneo\WatchExtension\Command;

use PhpSpec\Console\Command\RunCommand as BaseRunCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Lurker\ResourceWatcher;
use Lurker\Event\FilesystemEvent;
use Symfony\Component\Process\Process;

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
        $watcher->track('phpspec.specifications', 'spec/', FilesystemEvent::MODIFY);
        $watcher->track('phpspec.sources', 'src/', FilesystemEvent::MODIFY);

        $watcher->addListener('all', function (FilesystemEvent $event) use ($input, $output) {
            $process = new Process(sprintf(
                'clear && phpspec run -y --ansi %s',
                $event->getResource()
            ));
            $process->run();
            $output->writeln($process->getOutput());
        });

        $watcher->start();
    }
}

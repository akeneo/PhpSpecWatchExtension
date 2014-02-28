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
        $watcher->track('phpspec.specifications', 'spec/');

        $watcher->addListener('phpspec.specifications', function (FilesystemEvent $event) use ($input, $output) {
            $cmd = sprintf('phpspec run -fpretty %s', $event->getResource());
            $output->writeln('Executing ' . $cmd);
            $process = new Process($cmd);
            $process->run();
            $output->writeln($process->getOutput());
        });

        $watcher->start();
    }
}

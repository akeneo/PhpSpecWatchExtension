<?php

namespace Akeneo\WatchExtension;

use PhpSpec\Extension\ExtensionInterface;
use PhpSpec\ServiceContainer;
use Akeneo\WatchExtension\Command\RunCommand;

class AkeneoWatchExtension implements ExtensionInterface
{
    public function load(ServiceContainer $container)
    {
        $container->setShared('console.commands.run', function ($c) {
            return new RunCommand;
        });
    }
}

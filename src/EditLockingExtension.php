<?php

namespace Bolt\Extension\JKazimir\EditLocking;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Extension\SimpleExtension;
use Pimple as Container;

/**
 * EditLocking extension class.
 *
 * @author Jared Kazimir <jaredkazimir@gmail.com>
 */
class EditLockingExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
        return [
            (new JavaScript('edit-locking.js'))
                ->setZone(Zone::BACKEND)
                ->setLate(true),
            (new Stylesheet('edit-locking.css'))
                ->setZone(Zone::BACKEND),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerNutCommands(Container $container)
    {
        return [
            new SocketServeCommand($container)
        ];
    }
}

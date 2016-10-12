<?php

namespace Newtown\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\Util\ProcessExecutor;

class Script
{
    public static function postUpdate(Event $event)
    {
//        $composer = $event->getComposer();
    }

    public static function postAutoloadDump(Event $event)
    {
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();

        $processExecutor = new ProcessExecutor($event->getIO());
        $processExecutor->execute(sprintf('modman deploy %s', $installedPackage));
    }

    public static function postPackageUpdate(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();

        $processExecutor = new ProcessExecutor($event->getIO());
        $processExecutor->execute(sprintf('modman deploy %s', $installedPackage));
    }

    public static function warmCache(Event $event)
    {
        // make cache toasty
    }
}
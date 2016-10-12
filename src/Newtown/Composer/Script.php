<?php

namespace Newtown\Composer;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
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
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        $installedPackage = $operation->getPackage();

        if ($installedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('modman deploy %s', $installedPackage->getName()));
        }
    }

    public static function postPackageUpdate(PackageEvent $event)
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $installedPackage = $operation->getTargetPackage();

        if ($installedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('modman deploy %s', $installedPackage->getName()));
        }
    }

    public static function prePackageUninstall(PackageEvent $event)
    {
        /** @var UninstallOperation $operation */
        $operation = $event->getOperation();
        $installedPackage = $operation->getPackage();

        if ($installedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('modman undeploy %s', $installedPackage->getName()));
        }
    }

    public static function warmCache(Event $event)
    {
        // make cache toasty
    }
}
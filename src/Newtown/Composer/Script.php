<?php

namespace Newtown\Composer;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\PackageInterface;
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
            $processExecutor->execute(sprintf('modman deploy %s', static::getModmanName($installedPackage)));
        }
    }

    public static function postPackageUpdate(PackageEvent $event)
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $updatedPackage = $operation->getTargetPackage();

        if ($updatedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('modman deploy %s', static::getModmanName($updatedPackage)));
        }
    }

    public static function prePackageUninstall(PackageEvent $event)
    {
        /** @var UninstallOperation $operation */
        $operation = $event->getOperation();
        $uninstalledPackage = $operation->getPackage();

        if ($uninstalledPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('modman undeploy %s', static::getModmanName($uninstalledPackage)));
        }
    }

    public static function warmCache(Event $event)
    {
        // make cache toasty
    }

    protected static function getModmanName(PackageInterface $package)
    {
        $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        } else {
            $vendor = '';
            $name = $prettyName;
        }

        return "{$vendor}_{$name}";
    }
}
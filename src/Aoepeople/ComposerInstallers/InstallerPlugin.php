<?php

namespace Aoepeople\ComposerInstallers;

/**
 * This file is part of composer-installers for Magento.
 *
 * @package     composer-installers
 * @copyright   Copyright (c) 2017 Newtown-Web OG (http://www.newtown.at)
 * @author      Ingo Fabbri <if@newtown.at>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Util\ProcessExecutor;

class InstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }

    public static function getSubscribedEvents()
    {
        return array(
            'pre-update-cmd' => array(
                array('magentoEnableMaintenance'),
            ),
            'post-autoload-dump' => array(
                array('magentoFlushCache'),
                array('magentoRunSetup'),
                array('magentoDisableMaintenance'),
            ),
            'post-package-install' => array(
                array('postPackageInstall')
            ),
            'post-package-update' => array(
                array('postPackageUpdate')
            ),
            'pre-package-uninstall' => array(
                array('modmanUndeployPackage')
            )
        );
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        $installedPackage = $operation->getPackage();

        if ($installedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('%s/modman deploy %s', $binDir, static::getModmanName($installedPackage)));
        }
    }

    public static function postPackageUpdate(PackageEvent $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $updatedPackage = $operation->getTargetPackage();

        if ($updatedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('%s/modman deploy %s', $binDir, static::getModmanName($updatedPackage)));
        }
    }

    public function magentoEnableMaintenance(Event $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        $processExecutor = new ProcessExecutor($event->getIO());
        $processExecutor->execute($binDir . '/n98-magerun sys:maintenance --on');
    }

    public function magentoDisableMaintenance(Event $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        $processExecutor = new ProcessExecutor($event->getIO());
        $processExecutor->execute($binDir . '/n98-magerun sys:maintenance --off');
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

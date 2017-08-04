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
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\ProcessExecutor;

class InstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::PRE_UPDATE_CMD => array(
                array('magentoEnableMaintenance', 0),
            ),
            ScriptEvents::POST_INSTALL_CMD => array(
                array('magentoFlushCache'),
                array('magentoRunSetup'),
                array('magentoDisableMaintenance')
            ),
            ScriptEvents::POST_UPDATE_CMD => array(
                array('magentoFlushCache'),
                array('magentoRunSetup'),
                array('magentoDisableMaintenance')
            ),
            PackageEvents::POST_PACKAGE_UPDATE => array(
                array('postPackageUpdate', 0),
            ),
            PackageEvents::POST_PACKAGE_UNINSTALL => array(
                array('modmanUndeployPackage', 0),
            ),
            PackageEvents::POST_PACKAGE_INSTALL => array(
                array('postPackageInstall', 0),
            )
        );
    }

    public function postPackageInstall(PackageEvent $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        $installedPackage = $operation->getPackage();

        if ($installedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('%s/modman deploy %s --copy', $binDir,
                $this->getModmanName($installedPackage)));
        }
    }

    public function postPackageUpdate(PackageEvent $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $updatedPackage = $operation->getTargetPackage();

        if ($updatedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('%s/modman deploy %s --copy', $binDir,
                $this->getModmanName($updatedPackage)));
        }
    }

    public function modmanUndeployPackage(PackageEvent $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $updatedPackage = $operation->getTargetPackage();

        if ($updatedPackage->getType() === 'magento-module') {
            $processExecutor = new ProcessExecutor($event->getIO());
            $processExecutor->execute(sprintf('%s/modman undeploy %s', $binDir,
                $this->getModmanName($updatedPackage)));
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

    public function magentoFlushCache(Event $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        $processExecutor = new ProcessExecutor($event->getIO());
        $processExecutor->execute($binDir . '/n98-magerun cache:flush');
    }

    public function magentoRunSetup(Event $event)
    {
        $binDir = $event->getComposer()->getConfig()->get('bin-dir');

        $processExecutor = new ProcessExecutor($event->getIO());
        $processExecutor->execute($binDir . '/n98-magerun sys:setup:run');
    }

    protected function getModmanName(PackageInterface $package)
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

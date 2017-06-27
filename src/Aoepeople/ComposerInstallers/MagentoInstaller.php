<?php

namespace Aoepeople\ComposerInstallers;

use Composer\Composer;
use Composer\Package\PackageInterface;

class MagentoInstaller extends BaseInstaller
{
    protected $locations = array(
        'module' => '.modman/{$vendor}_{$name}/'
    );

    /**
     * @param PackageInterface $package
     * @param Composer $composer
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(PackageInterface $package = null, Composer $composer = null)
    {
        parent::__construct($package, $composer);

        if ($package === null) {
            return;
        }
        
        $packageExtra = $package->getExtra();

        if (isset($packageExtra['magento-core-package-type'])) {
            //merge excludes from root package composer.json file with default excludes
            if (!isset($packageExtra['magento-root-dir'])) {
                throw new \InvalidArgumentException('magento-root-dir must be specified in root package');
            }

            $this->locations[$packageExtra['magento-core-package-type']] = rtrim($packageExtra['magento-root-dir'],
                '/');
        }
    }
}

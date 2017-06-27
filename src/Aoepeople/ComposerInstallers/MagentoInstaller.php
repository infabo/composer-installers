<?php

namespace Aoepeople\ComposerInstallers;

class MagentoInstaller extends BaseInstaller
{
    protected $locations = array(
        'module' => '.modman/{$vendor}_{$name}/'
    );

    /**
     * @param array $packageExtra
     */
    public function __construct(array $packageExtra)
    {
        parent::__construct();

        if (isset($packageExtra['magento-core-package-type'])) {
            //merge excludes from root package composer.json file with default excludes
            if (!isset($packageExtra['magento-root-dir'])) {
                throw new \InvalidArgumentException("magento-root-dir must be specified in root package");
            }

            $this->locations[$packageExtra['magento-core-package-type']] = rtrim($packageExtra['magento-root-dir'],
                '/');
        }
    }
}

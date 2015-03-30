<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Setup\ConfigOptionsListInterface;

/**
 * Collects all ConfigOptionsList class in modules and setup
 */
class ConfigOptionsListCollector
{
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Module list including enabled and disabled modules
     *
     * @var FullModuleList
     */
    private $fullModuleList;

    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param FullModuleList $fullModuleList
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem,
        FullModuleList $fullModuleList,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->fullModuleList = $fullModuleList;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Auto discover ConfigOptionsList class and collect them.
     * These classes should reside in <module>/Setup directories.
     *
     * @return \Magento\Framework\Setup\ConfigOptionsListInterface[]
     */
    public function collectOptionLists()
    {
        $optionsList = [];

        // go through modules
        foreach ($this->fullModuleList->getNames() as $moduleName) {
            $optionsClassName = str_replace('_', '\\', $moduleName) . '\Setup\ConfigOptionsList';
            if (class_exists($optionsClassName)) {
                $optionsClass = $this->objectManagerProvider->get()->create($optionsClassName);
                if ($optionsClass instanceof ConfigOptionsListInterface) {
                    $optionsList[$moduleName] = $optionsClass;
                }
            }
        }

        // check Framework
        $setupOptionsClassName = 'Magento\Framework\Config\ConfigOptionsList';
        if (class_exists($setupOptionsClassName)) {
            $setupOptionsClass = $this->objectManagerProvider->get()->create($setupOptionsClassName);
            if ($setupOptionsClass instanceof ConfigOptionsListInterface) {
                $optionsList['setup'] = $setupOptionsClass;
            }
        }

        return $optionsList;
    }
}

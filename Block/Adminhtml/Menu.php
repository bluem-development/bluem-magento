<?php

namespace Bluem\Integration\Block\Adminhtml;

use Magento\Backend\Block\Menu as BackendMenu;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\ModuleListInterface;

class Menu extends BackendMenu
{
    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        ModuleListInterface $moduleList,
        array $data = []
    ) {
        $this->_moduleList = $moduleList;
        parent::__construct($context, $directoryList, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $version = $this->_moduleList->getOne('Bluem_Integration')['setup_version'];
        $html = parent::_toHtml();
        $html .= '<div style="text-align:center; margin-top: 10px;">Version: '.$version.'</div>';
        return $html;
    }
}

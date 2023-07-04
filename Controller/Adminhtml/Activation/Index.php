<?php

namespace Bluem\Integration\Controller\Adminhtml\Activation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var $resultPageFactory
     */
    protected $resultPageFactory = false;
    
    /**
     * Constructor
     * 
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    
    /**
     * Execute
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bluem_Integration::menu');
        $resultPage->getConfig()->getTitle()->prepend(
            __('Bluem &middot; Activation')
        );
        return $resultPage;
    }
    
    /**
     * Is allowed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bluem_Integration::menu');
    }
}

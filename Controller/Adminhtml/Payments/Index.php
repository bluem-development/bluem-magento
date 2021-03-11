<?php

namespace Bluem\Integration\Controller\Adminhtml\Payments;

class Index extends \Magento\Backend\App\Action
{
        protected $resultPageFactory = false;      
        public function __construct(
                \Magento\Backend\App\Action\Context $context,
                \Magento\Framework\View\Result\PageFactory $resultPageFactory
        ) {
                parent::__construct($context);
                $this->resultPageFactory = $resultPageFactory;
        } 
        public function execute()
        {
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('Bluem_Integration::menu');
                $resultPage->getConfig()->getTitle()->prepend(__('Bluem &middot; Payments'));
                return $resultPage;
        }
        protected function _isAllowed()
        {
                return $this->_authorization->isAllowed('Bluem_Integration::menu');
        }
}
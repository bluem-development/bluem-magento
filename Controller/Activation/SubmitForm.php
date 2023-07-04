<?php

namespace Bluem\Integration\Controller\Activation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class SubmitForm extends Action
{
    /**
     * @var $resultPageFactory
     */
    protected $resultPageFactory;

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
        $resultPage->getConfig()->getTitle()->set(__('Submit Form'));
        return $resultPage;
    }
}

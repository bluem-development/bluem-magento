<?php

namespace Bluem\Integration\Controller\Adminhtml\Activation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;

class SaveData extends Action
{
    /**
     * @var $resultRedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var $transportBuilder
     */
    protected $transportBuilder;
    
    /**
     * @var $storeManager
     */
    protected $storeManager;

    /**
     * @var $request
     */
    protected $request;

    /**
     * Constructor
     * 
     * @param Context $context
     * @param RedirectFactory $resultRedirectFactory
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        RequestInterface $request
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $postData = $this->request->getPostValue();

        // Send email with form data
        $this->sendEmail($postData);

        // Redirect to a success page
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('bluemadmin/activation/success');
        return $resultRedirect;
    }

    /**
     * Send the email
     * 
     * @param $data
     */
    protected function sendEmail($data)
    {
        // Build your email template and send the email using Magento's TransportBuilder
        $templateVars = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('activation_email_bluem')
            ->setTemplateOptions(['area' => 'frontend', 'store' => 1])
            ->setTemplateVars($templateVars)
            ->setFrom('general')
            ->addTo('p.meester@bluem.nl')
            ->getTransport();

        $transport->sendMessage();
    }

    /**
     * Is allowed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bluem_Integration::menu');
    }
}

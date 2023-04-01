<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

use Bluem\BluemPHP\Bluem as Bluem;

class Getbanks extends Action
{
    /**
     * @var Bluem
     */
    protected $_bluem;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;

        $bluem_config = new stdClass;

        $this->_bluem = new Bluem($bluem_config);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $banks = []; // Retrieve the bank data from your own API and set it to $banks

        $bics = $this->_bluem->retrieveBICCodesForContext('Payments');

        $result = $this->resultJsonFactory->create();
        return $result->setData($banks);
    }
}

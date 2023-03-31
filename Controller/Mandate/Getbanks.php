<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Controller\Mandate;

use Bluem\Integration\Controller\BluemAction;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Getbanks extends BluemAction
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $banks = []; // Retrieve the bank data from your own API and set it to $banks

        $bics = $this->_bluem->retrieveBICCodesForContext('Mandates');
        var_dump($bics);

        $result = $this->resultJsonFactory->create();
        return $result->setData($banks);
    }
}

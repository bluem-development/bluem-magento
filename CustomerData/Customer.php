<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Peter Meester <p.meester@bluem.nl>
 */

namespace Bluem\Integration\CustomerData;

use Bluem\Integration\Helper\Data as DataHelper;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Customer data source
 */
class Customer extends \Magento\Customer\CustomerData\Customer implements SectionSourceInterface
{
    /**
     * @var \Bluem\Integration\Helper\Data
     */
    protected $_dataHelper;
    
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var View
     */
    protected $customerViewHelper;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        View $customerViewHelper,
        DataHelper $dataHelper
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
        
        $this->_dataHelper = $dataHelper;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return [];
        }
        $customer = $this->currentCustomer->getCustomer();
        $identity_valid = $this->_dataHelper->getIdentityValid();
        return [
            'identity_valid' => $identity_valid->valid ? true : false,
            'fullname' => $this->customerViewHelper->getCustomerName($customer),
            'firstname' => $customer->getFirstname(),
        ];
    }
}

<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Peter Meester <p.meester@bluem.nl>
 */

namespace Bluem\Integration\CustomerData;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Customer data source
 */
class Customer extends \Magento\Customer\CustomerData\Customer implements SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var View
     */
    private $customerViewHelper;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        View $customerViewHelper
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
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
        return [
            'fullname' => $this->customerViewHelper->getCustomerName($customer),
            'firstname' => $customer->getFirstname(),
        ];
    }
}
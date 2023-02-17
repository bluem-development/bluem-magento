<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Block\Form;

use Magento\Payment\Block\Form;

class EPaymentSofort extends Form
{
    /**
     * EPayment template
     * This is used for both frontend and backend
     * I created two files named epayment.phtml in the path view\adminhtml\templates\form and view\frontend\templates\form because it has different content.
     *
     * @var string
     */
    protected $_template = 'Bluem_Integration::form/epayment-sofort.phtml';
}

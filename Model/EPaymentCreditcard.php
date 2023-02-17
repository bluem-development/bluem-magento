<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

/* Based on  php-cuong/magento-offline-payments  */

namespace Bluem\Integration\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\PaymentInterface;

class EPaymentCreditcard extends AbstractMethod
{
    const PAYMENT_METHOD_EPAYMENT_CODE = 'epayment_creditcard';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_EPAYMENT_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = \Bluem\Integration\Block\Form\EPaymentCreditcard::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Bluem\Integration\Block\Info\EPaymentCreditcard::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
}

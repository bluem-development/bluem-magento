<?php
/* Based on  php-cuong/magento-offline-payments  */

namespace Bluem\Integration\Model;

use Magento\Quote\Api\Data\PaymentInterface;

class PdqPayment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_PDQPAYMENT_CODE = 'pdqpayment';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_PDQPAYMENT_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = \Bluem\Integration\Block\Form\PdqPayment::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Bluem\Integration\Block\Info\PdqPayment::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
}

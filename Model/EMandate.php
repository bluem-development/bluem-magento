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

class EMandate extends AbstractMethod
{
    public const PAYMENT_METHOD_EMANDATE_CODE = 'emandate';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_EMANDATE_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = \Bluem\Integration\Block\Form\EMandate::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Bluem\Integration\Block\Info\EMandate::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
}

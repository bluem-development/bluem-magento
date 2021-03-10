<?php
/* Based on  php-cuong/magento-offline-payments  */

namespace Bluem\Integration\Block\Info;

class PdqPayment extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Bluem_Integration::info/pdqpayment.phtml';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Bluem_Integration::info/pdf/pdqpayment.phtml');
        return $this->toHtml();
    }
}

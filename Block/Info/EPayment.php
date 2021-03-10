<?php
/* Based on  php-cuong/magento-offline-payments  */

namespace Bluem\Integration\Block\Info;

class EPayment extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Bluem_Integration::info/epayment.phtml';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Bluem_Integration::info/pdf/epayment.phtml');
        return $this->toHtml();
    }
}

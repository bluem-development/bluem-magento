<?php

namespace Bluem\Integration\Block\Form;

class EPayment extends \Magento\Payment\Block\Form
{
    /**
     * EPayment template
     * This is used for both frontend and backend
     * I created two files named epayment.phtml in the path view\adminhtml\templates\form and view\frontend\templates\form because it has different content.
     *
     * @var string
     */
    protected $_template = 'Bluem_Integration::form/epayment.phtml';
}

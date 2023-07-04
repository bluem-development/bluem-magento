<?php

namespace Bluem\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Activation extends Template
{
    /**
     * Constructor
     * 
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Get form action
     */
    public function getFormAction()
    {
        return $this->getUrl('module/activation/savedata', ['_secure' => true]);
    }
}

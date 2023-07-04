<?php

namespace Bluem\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;

class Activation extends Template
{
    /**
     * @var $formKey
     */
    protected $formKey;

    /**
     * Constructor
     * 
     * @param Context $context
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Get form action
     */
    public function getFormAction()
    {
        return $this->getUrl('bluemadmin/activation/savedata', ['_secure' => true]);
    }

    /**
     * Get form key
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}

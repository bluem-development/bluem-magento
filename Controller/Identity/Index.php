<?php

namespace Bluem\Integration\Controller\Identity;

use Bluem\Integration\Controller\BluemAction;

// require __DIR__ . '/../BluemAction.php';

class Index extends BluemAction
{
    /**
     * Prints the Identity from informed order id
     * @return Page
     * @throws LocalizedException
     */
    public function execute()
    {
        return $this->_pageFactory->create();
    }
}

// @todo: add iDIN as checkout step https://devdocs.magento.com/guides/v2.4/howdoi/checkout/checkout_new_step.html

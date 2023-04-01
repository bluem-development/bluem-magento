<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Controller\Payment;

use Bluem\Integration\Controller\BluemAction;

use Bluem\BluemPHP\Bluem as Bluem;

class Getbanks extends BluemAction
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $banks = [];

        $bics = $this->_bluem->retrieveBICsForContext('Mandates');
        
        // Loop through BICs
        foreach ($bics as $bic) {
            $banks[] = [
                'label' => $bic->issuerName,
                'value' => $bic->issuerID,
            ];
        }

        header("Content-type: application/json; charset=utf-8");
        echo json_encode($banks);
        exit;
    }
}

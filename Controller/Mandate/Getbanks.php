<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Controller\Mandate;

use Bluem\Integration\Controller\BluemAction;

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
        var_dump($bics, $banks);
    }
}

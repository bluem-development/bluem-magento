<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Peter Meester <p.meester@bluem.nl>
 */

namespace Bluem\Integration\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;
    
    /**
     * @param EavSetupFactory  $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
    }
    
	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
        $setup->startSetup();
        
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        
		// if (version_compare($context->getVersion(), '0.5.13', '<')) {
			// @todo; get code from module setting if possible (maybe not because this will be executed during install..
            $attribute_code = 'agecheck_required';
            
            if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $attribute_code)) {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attribute_code,
                    [
                        'type' => 'int',
                        'backend' => '',
                        'frontend' => '',
                        'label' => 'Age verification',
                        'input' => 'boolean',
                        'class' => '',
                        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => true,
                        'user_defined' => false,
                        'default' => '0',
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'apply_to' => ''
                    ]
                );
            }
		//}
        $setup->endSetup();
	}
}

<?php
namespace Bluem\Integration\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

}



// public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
// {
//     $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
//         $eavSetup->addAttribute(
//             \Magento\Catalog\Model\Product::ENTITY,
//             'agecheck_required',
//             [
//                 'group' => 'General',
//                 'type' => 'varchar',
//                 'label' => 'Identity check required',
//                 'input' => 'select',
//                 'source' => 'Bluem\Integration\Model\Attribute\Source\AgeCheckRequired',
//                 'frontend' => 'Bluem\Integration\Model\Attribute\Frontend\AgeCheckRequired',
//                 'backend' => 'Bluem\Integration\Model\Attribute\Backend\AgeCheckRequired',
//                 'required' => false,
//                 'sort_order' => 50,
//                 'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
//                 'is_used_in_grid' => false,
//                 'is_visible_in_grid' => false,
//                 'is_filterable_in_grid' => false,
//                 'visible' => true,
//                 'is_html_allowed_on_front' => true,
//                 'visible_on_front' => true
//             ]
//         );
// }
<?php   
 /* @var $installer Mage_Customer_Model_Entity_Setup */
    $installer = $this;
    $installer->startSetup();
    /* @var $addressHelper Mage_Customer_Helper_Address */
    $addressHelper = Mage::helper('customer/address');
    $store         = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID);
     
    /* @var $eavConfig Mage_Eav_Model_Config */
    $eavConfig = Mage::getSingleton('eav/config');
     
    // update customer address user defined attributes data
    $attributes = array(
        'bairro'           => array(   
            'label'    => 'Bairro',
            'type'     => 'varchar',
            'input'    => 'text',
            'is_user_defined'   => 1,
            'is_system'         => 0,
            'is_visible'        => 1,
            'sort_order'        => 140,
            'is_required'       => 1,
            'multiline_count'   => 0,
            'validate_rules'    => array(
                'max_text_length'   => 255,
                'min_text_length'   => 1
            ),
        ),
        'numero'           => array(   
            'label'    => 'Número',
            'type'     => 'varchar',
            'input'    => 'multiline',
            'is_user_defined'   => 1,
            'is_system'         => 0,
            'is_visible'        => 1,
            'sort_order'        => 140,
            'is_required'       => 1,
            'multiline_count'   => 0,
            'validate_rules'    => array(
                'max_text_length'   => 10,
                'min_text_length'   => 1
            ),
        ),
    );

    foreach ($attributes as $attributeCode => $data) {
        $attribute = $eavConfig->getAttribute('customer_address', $attributeCode);
        $attribute->setWebsite($store->getWebsite());
        $attribute->addData($data);
            $usedInForms = array(
                'adminhtml_customer_address',
                'customer_address_edit',
                'customer_register_address'
            );
            $attribute->setData('used_in_forms', $usedInForms);
        $attribute->save();
    }
     
    $installer->run("
        ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `bairro` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
         ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `bairro` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
        ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `numero` VARCHAR(10) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
         ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `numero` VARCHAR(10) CHARACTER SET utf8 DEFAULT NULL AFTER `fax`;
        ");
    $installer->endSetup();
?>

<?php
/**
 * Magento
 * /www/loja/app/code/local/Conamore/Customer/Model/Entity
 */


/**
 * Customer resource setup model
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class Conamore_Customer_Model_Entity_Setup extends Mage_Customer_Model_Entity_Setup
{

    public function getDefaultEntities()
    {
        return array(
            'customer' => array(
                'entity_model'          =>'customer/customer',
                'table'                 => 'customer/entity',
                'increment_model'       => 'eav/entity_increment_numeric',
                'increment_per_store'   => false,
                'additional_attribute_table' => 'customer/eav_attribute',
                'entity_attribute_collection' => 'customer/attribute_collection',
                'attributes' => array(
//                    'entity_id'         => array('type'=>'static'),
//                    'entity_type_id'    => array('type'=>'static'),
//                    'attribute_set_id'  => array('type'=>'static'),
//                    'increment_id'      => array('type'=>'static'),
//                    'created_at'        => array('type'=>'static'),
//                    'updated_at'        => array('type'=>'static'),
//                    'is_active'         => array('type'=>'static'),

                    'website_id' => array(
                        'type'          => 'static',
                        'label'         => 'Associate to Website',
                        'input'         => 'select',
                        'source'        => 'customer/customer_attribute_source_website',
                        'backend'       => 'customer/customer_attribute_backend_website',
                        'sort_order'    => 10,
                    ),
                    'store_id' => array(
                        'type'          => 'static',
                        'label'         => 'Create In',
                        'input'         => 'select',
                        'source'        => 'customer/customer_attribute_source_store',
                        'backend'       => 'customer/customer_attribute_backend_store',
                        'visible'       => false,
                        'sort_order'    => 20,
                    ),
                    'created_in' => array(
                        'type'          => 'varchar',
                        'label'         => 'Created From',
                        'sort_order'    => 30,
                    ),
                    'prefix' => array(
                        'label'         => 'Prefix',
                        'required'      => false,
                        'sort_order'    => 37,
                    ),
                    'firstname' => array(
                        'label'         => 'First Name',
                        'sort_order'    => 40,
                    ),
                    'middlename' => array(
                        'label'         => 'Middle Name/Initial',
                        'required'      => false,
                        'sort_order'    => 43,
                    ),
                    'lastname' => array(
                        'label'         => 'Last Name',
                        'sort_order'    => 50,
                    ),
                    'suffix' => array(
                        'label'         => 'Suffix',
                        'required'      => false,
                        'sort_order'    => 53,
                    ),
                    'email' => array(
                        'type'          => 'static',
                        'label'         => 'Email',
                        'class'         => 'validate-email',
                        'sort_order'    => 60,
                    ),
                    'group_id' => array(
                        'type'          => 'static',
                        'input'         => 'select',
                        'label'         => 'Group',
                        'source'        => 'customer/customer_attribute_source_group',
                        'sort_order'    => 70,
                    ),
                    'dob' => array(
                        'type'          => 'datetime',
                        'input'         => 'date',
                        'backend'       => 'eav/entity_attribute_backend_datetime',
                        'required'      => false,
                        'label'         => 'Date Of Birth',
                        'sort_order'    => 80,
                    ),
                    'password_hash' => array(
                        'input'         => 'hidden',
                        'backend'       => 'customer/customer_attribute_backend_password',
                        'required'      => false,
                    ),
                    'default_billing' => array(
                        'type'          => 'int',
                        'visible'       => false,
                        'required'      => false,
                        'backend'       => 'customer/customer_attribute_backend_billing',
                    ),
                    'default_shipping' => array(
                        'type'          => 'int',
                        'visible'       => false,
                        'required'      => false,
                        'backend'       => 'customer/customer_attribute_backend_shipping',
                    ),
                    'taxvat' => array(
                        'label'         => 'Tax/Vat number',
                        'visible'       => true,
                        'required'      => false,
                    ),
                    'customrg' => array(
                        'label'         => 'RG',
                        'visible'       => true,
                        'required'      => true,
                    ),
						  'razaosocial' => array(
							  'label'		=> 'Razão Social',
							  'visible'		=> true,
							  'required'	=> false,
						  ),
                    'customcpf' => array(
                        'label'         => 'CPF',
                        'visible'       => true,
                        'required'      => true,
                    ),
                    'customcnpj' => array(
                        'label'         => 'CNPJ',
                        'visible'       => true,
                        'required'      => false,
                    ),
                    'inscricaoestadual' => array(
                        'label'         => 'Inscrição Estadual',
                        'visible'       => true,
                        'required'      => false,
                    ),
                    'confirmation' => array(
                        'label'         => 'Is Confirmed',
                        'visible'       => false,
                        'required'      => false,
                    ),
                    'created_at' => array(
                        'type'          => 'static',
                        'label'         => 'Created At',
                        'visible'       => false,
                        'required'      => false,
                        'input'         => 'date',
                    ),
                ),
            ),

            'customer_address'=>array(
                'entity_model'  =>'customer/customer_address',
                'table' => 'customer/address_entity',
                'additional_attribute_table' => 'customer/eav_attribute',
                'entity_attribute_collection' => 'customer/address_attribute_collection',
                'attributes' => array(
//                    'entity_id'         => array('type'=>'static'),
//                    'entity_type_id'    => array('type'=>'static'),
//                    'attribute_set_id'  => array('type'=>'static'),
//                    'increment_id'      => array('type'=>'static'),
//                    'parent_id'         => array('type'=>'static'),
//                    'created_at'        => array('type'=>'static'),
//                    'updated_at'        => array('type'=>'static'),
//                    'is_active'         => array('type'=>'static'),

                    'prefix' => array(
                        'label'         => 'Prefix',
                        'required'      => false,
                        'sort_order'    => 7,
                    ),
                    'firstname' => array(
                        'label'         => 'First Name',
                        'sort_order'    => 10,
                    ),
                    'middlename' => array(
                        'label'         => 'Middle Name/Initial',
                        'required'      => false,
                        'sort_order'    => 13,
                    ),
                    'lastname' => array(
                        'label'         => 'Last Name',
                        'sort_order'    => 20,
                    ),
                    'suffix' => array(
                        'label'         => 'Suffix',
                        'required'      => false,
                        'sort_order'    => 23,
                    ),
                    'company' => array(
                        'label'         => 'Company',
                        'required'      => false,
                        'sort_order'    => 30,
                    ),
                    'street' => array(
                        'type'          => 'text',
                        'backend'       => 'customer_entity/address_attribute_backend_street',
                        'input'         => 'multiline',
                        'label'         => 'Street Address',
                        'sort_order'    => 40,
                    ),
                    'city' => array(
                        'label'         => 'City',
                        'sort_order'    => 50,
                    ),
                    'country_id' => array(
                        'type'          => 'varchar',
                        'input'         => 'select',
                        'label'         => 'Country',
                        'class'         => 'countries',
                        'source'        => 'customer_entity/address_attribute_source_country',
                        'sort_order'    => 60,
                    ),
                    'region' => array(
                        'backend'       => 'customer_entity/address_attribute_backend_region',
                        'label'         => 'State/Province',
                        'class'         => 'regions',
                        'sort_order'    => 70,
                    ),
                    'region_id' => array(
                        'type'          => 'int',
                        'input'         => 'hidden',
                        'source'        => 'customer_entity/address_attribute_source_region',
                        'required'      => 'false',
                        'sort_order'    => 80,
                        'label'         => 'State/Province'
                    ),
                    'postcode' => array(
                        'label'         => 'Zip/Postal Code',
                        'sort_order'    => 90,
                    ),
                    'telephone' => array(
                        'label'         => 'Telephone',
                        'sort_order'    => 100,
                    ),
                    'fax' => array(
                        'label'         => 'Fax',
                        'required'      => false,
                        'sort_order'    => 110,
                    ),
                ),
            ),
        );
    }

}

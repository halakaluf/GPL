<?php

class Conamore_Gpl_Model_Source_ContratoTipo
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'BUY_PAGE_CIELO', 'label'=>Mage::helper('adminhtml')->__('Buy Page Cielo')),
            array('value'=>'BUY_PAGE_LOJA', 'label'=>Mage::helper('adminhtml')->__('Buy Page Loja')),
        );
    }

}

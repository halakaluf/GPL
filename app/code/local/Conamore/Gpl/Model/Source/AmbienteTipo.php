<?php

class Conamore_Gpl_Model_Source_AmbienteTipo
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'TESTE', 'label'=>Mage::helper('adminhtml')->__('Teste')),
            array('value'=>'PRODUCAO', 'label'=>Mage::helper('adminhtml')->__('Produção')),
        );
    }

}

<?php

class Conamore_Gpl_Block_Form_Dadosboleto extends Mage_Core_Block_Template
{

    /**
     * Internal constructor
     * Set info template for payment step
     *
     */
    protected function _construct()    {
        parent::_construct();
        $this->setTemplate('gpl/form/frm_dados_boleto.phtml');
    }

}



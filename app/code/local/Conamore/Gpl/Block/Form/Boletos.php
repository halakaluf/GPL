<?php

class Conamore_Gpl_Block_Form_Boletos extends Mage_Payment_Block_Form_Cc
{

    /**
     * Internal constructor
     * Set info template for payment step
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('gpl/form/frm_boletos.phtml');
    }


}



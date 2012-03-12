<?php

class Conamore_Gpl_Block_Form_Dadoscartao extends Mage_Core_Block_Template
{

    /**
     * Internal constructor
     * Set info template for payment step
     *
     */
    protected function _construct()    {
        parent::_construct();
        $this->setTemplate('gpl/form/frm_dados.phtml');
    }

	 public function getDadosPagamento(){

		$dados = $this->getPayment()->getAdditionalData();


		$body = "";
		$body .= "<p>Lala Testar</p>";
		$body .= "<p>" .$dados['num_parcelas']. "</p>";
		
		return $body;
	

	}


	public function getPayment(){
		return $MethodPagamento = Mage::getSingleton('core/session')->getDadosPagamento();
	}



}



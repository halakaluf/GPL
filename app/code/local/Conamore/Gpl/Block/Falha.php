<?php

class Conamore_Gpl_Block_Falha extends Mage_Core_Block_Template
{

    /**
     * Internal constructor
     * Set info template for payment step
     *
     */

	 protected $_order = null;


    protected function _construct()    {
        parent::_construct();
        $this->setTemplate('gpl/falha.phtml');
    }



	 public function geraHtml() {

		$body = "";

		$body .= "<h1>Prezado ". $this->_getOrder()->getCustomerFirstname(). " ".$this->_getOrder()->getCustomerLastname()." sua compra foi realizada com sucesso!</h1>";
		$body .= "<h1>Um email com os dados da compra foi enviado para você.</h1>";

		return $body;
		

	}

    public function getErrorMessage (){
		  if($this->getErro()){
				$erroArray = $this->getErro();

				return $erroArray['erro']['mensagem'];
		  }
        $error = Mage::getSingleton('checkout/session')->getErrorMessage();
        return $error;
    }

    public function getRealOrderId(){
        return Mage::getSingleton('checkout/session')->getLastRealOrderId();
    }


    public function getContinueShoppingUrl(){
        return Mage::getUrl('checkout/cart');
    }


}



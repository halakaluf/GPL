<?php

class Conamore_Gpl_Block_Form_Sucesso extends Mage_Core_Block_Template
{

    /**
     * Internal constructor
     * Set info template for payment step
     *
     */

	 protected $_order = null;


    protected function _construct()    {
        parent::_construct();
        $this->setTemplate('gpl/sucesso.phtml');
    }



	 public function geraHtml() {

		$body = "";

		$body .= "<h1>Prezado ". $this->_getOrder()->getCustomerFirstname(). " ".$this->_getOrder()->getCustomerLastname()." sua compra foi realizada com sucesso!</h1>";
		$body .= "<h1>Um email com os dados da compra foi enviado para você.</h1>";

		return $body;
		

	}

	public function enviaEmail(){
				try{
Mage::log("Tentou enviar email");
Mage::log($this->_order->debug());
					$this->_order->sendNewOrderEmail();

				} catch (Exception $ex) {  
					Mage::log($ex);
				}

	}

	protected function _getOrder(){

		if(!$this->_order){
			Mage::log("Pegou Order");
			$order = new Mage_Sales_Model_Order();
			$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
			$order->loadByIncrementId($orderId);
Mage::log($order->debug());
			$_order = $order;
		}
		return

	}

}



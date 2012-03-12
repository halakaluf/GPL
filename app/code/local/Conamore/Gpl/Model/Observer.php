<?php

class Conamore_Gpl_Model_Observer {
    /**
     * Set forced canCreditmemo flag
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Payment_Model_Observer
     */
    public function salesOrderBeforeSave(Varien_Event_Observer $observer) {
		  Mage::log("-------------------------------------------E aqui mesmo inicio-------------------------------------------");
		  Mage::log($observer->debug());
		  Mage::log("-------------------------------------------E aqui mesmo fim-------------------------------------------");
        return $this;
    }



    public function salesOrderSave(Varien_Event_Observer $observer) {

$payment = $observer->getEvent()->getOrder()->getPayment();
//$customer = Mage::getModel('customer/customer')->load($observer->getEvent()->getOrder()->getCustomerId());
//Mage::log($customer->debug());
//Mage::log($observer->getEvent()->getOrder()->getShippingAddress()->debug());
//exit;
//$observer->getEvent()->getOrder()->setAdditionalData($payment->getAdditionalData());
//$observer->getEvent()->getOrder()->setNumParcelas(120);
//$observer->getEvent()->getOrder()->save();
//$additionalData = $payment->getAdditionalData();
//$details_payment = array();
//$details_payment['num_parcelas'] = $data->getNumParcelas();
//$order->setAdditionalData($additionalData)->save();

//$payment_nw = new Mage_Sales_Model_Order_Payment();
//$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
//$observer->getEvent()->getOrder()->get
//Mage::log($orderId);
//exit;
//$payment_nw->loadByParentId(81);
//Mage::log($payment_nw->debug());
//exit;




Mage::getSingleton('core/session')->setDadosPagamento($payment);

//$data = Mage::registry('payment_data');

//Mage::log($observer->getEvent()->getCreditmemoItem()->debug());
//$observer->getEvent()->getOrder()->setNumParcelas($payment->getNumParcelas());
//Mage::log($observer->getEvent()->getOrder()->debug());
//exit;
//Mage::log(Mage::getUrl("gpl/gpl/dadoscartao"));
//Mage::app()->getResponse()->setRedirect(Mage::getUrl("gpl/gpl/dadoscartao"));
//Mage::app()->getResponse()->sendResponse(); 

//exit;

//exit;
//vejo qual e o tipo do cartao
/*
		$cc_type = $observer->getEvent()->getOrder()->getPayment()->getCcType();
		$retorno = "";
		switch ($cc_type){
			case "VI":
			case "MC":
				$this->_processVisaMaster($observer);
			break;
			case "AE":
				$this->_processAmex($observer);
			break;

		}
*/
      // $order = $observer->getEvent()->getOrder();
//		 $this->_getURL($observer);
//$MethodPagamento = $this->_getRequest()->getPost('payment');
//$customer = $event->getCustomer();

//$dados = $payment->getAdditionalData();

//Mage::log($dados['num_parcelas']);
//exit;


//Mage::log($order->getQuote()->debug());

//Mage::log("------------------------opa----------------------------------------------");
        return $this;
    }



}

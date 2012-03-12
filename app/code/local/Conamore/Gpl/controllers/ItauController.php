<?php

class Conamore_Gpl_ItauController extends Mage_Core_Controller_Front_Action {

	public function retornoAction(){

		if($this->getRequest()->isGet()) {
			Mage::log("------------RETORNO ITAU---------------");
//			$order = new Mage_Sales_Model_Order();
			$DC = $this->getRequest()->getParam('DC');
//			$order->loadByIncrementId($orderId);

//			$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
			Mage::log($DC);
			if($this->validaRetorno()){

				// Monta a variável com os dados para postagem

				$request = 'identificacao=' . Mage::getStoreConfig('payment/redecard/shop_id');
				$request .= '&ambiente='.Mage::getStoreConfig('payment/boletos/ambiente_tipo');
				$request .= '&modulo=' . $transorig;
				$request .= '&operacao=Retorno';
				$request .= '&DC=' . $DC;
				$request .= '&URLRETORNO=' . Mage::getUrl('gpl/itau/retornodadositau');

				//faco o post para a redecard para confirmar a transação
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://comercio.locaweb.com.br/comercio.comp');
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close($ch);
			Mage::log("------------RETORNO ITAU---------------");
			
			}
		}

	}


	public function retornodadositauAction(){
		Mage::log("------------RETORNO DADOS ITAU---------------");
		if($this->getRequest()->isGet()) {


			$order = new Mage_Sales_Model_Order();
			$numPedido = $this->getRequest()->getParam('pedido');
			$tipoPagamento = $this->getRequest()->getParam('tipPag');
			$order->load($numPedido);
			Mage::log($order->debug());
			if($order){

				$this->_createInvoice($order);
		     // $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'processing', 'Informado pagamento do boleto através do Itau Shopline', false);
				if ($order->getId()) {
					try {
						$order->sendNewOrderEmail();
					} catch (Exception $e) {
						Mage::log("ERROR Arquivo: Conamore_Gpl_ItauController linha 91");
						Mage::log("ERRORMSG Nao foi possivel enviar email NewOrderEmail para essa order: ".$order->getIncrementId());
						Mage::log($e->getMessage());
					}
				}
				$order->setEmailSent(true);
				$order->save();
			}

		}
		Mage::log("------------RETORNO DADOS ITAU---------------");
	}


	private function _createInvoice($pOrder){
		try{

			if(!$pOrder->canInvoice())	{
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
			}
			$invoice = Mage::getModel('sales/service_order', $pOrder)->prepareInvoice();
			 
			if (!$invoice->getTotalQty()) {
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
			}
			 
			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
			$invoice->register();
			$transactionSave = Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder());
			 
			$transactionSave->save();
		}
		catch (Mage_Core_Exception $e) {
			Mage::log("ERROR Arquivo: Conamore_Gpl_ItauController linha 84");
			Mage::log("ERRORMSG Nao foi possivel criar um invoice para essa order: ".$pOrder->getIncrementId());
			Mage::log($e->getMessage());
		}


	}


	private function validaRetorno(){
		$codigo_retorno = $this->getRequest()->getParam('DC');
		if(!$codigo_retorno){
				return false;
		}
		
		return true;
	}

	public function dadosboletoAction()	{


		$this->loadLayout();
		$block = $this->getLayout()->createBlock('Conamore_Gpl_Block_Form_Dadosboleto','formBoleto');
//		Mage::log($block);
		//$block = $this->getLayout()->createBlock('Conamore_Gpl_Block_Form_Dadoscartao','formCartao',array('template' => 'gpl/form/frm_dados.phtml'));
		//$blockR = $this->getLayout()->createBlock('Mage_Core_Block_Template','infoCompra',array('template' => 'gpl/info/dados_compra.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
		$this->getLayout()->getBlock('head')->setTitle("Impressão do Boleto");
		//$this->getLayout()->getBlock('right')->insert($blockR, 'catalog.compare.sidebar', true);
		//$this->getLayout()->getBlock('right')->append($blockR);
		$this->renderLayout();

	}



}

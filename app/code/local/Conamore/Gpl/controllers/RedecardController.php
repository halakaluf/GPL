<?php

class Conamore_Gpl_RedecardController extends Mage_Core_Controller_Front_Action {

	public function retornoAction(){
		if($this->getRequest()->isGet()) {
			$order = new Mage_Sales_Model_Order();
			$orderId = $this->getRequest()->getParam('NUMPEDIDO');
			$order->loadByIncrementId($orderId);
			$paramValue = $this->getRequest()->getParam('NUMAUTOR');
			$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

			if($this->validaRetorno()){
				$parcelas = $quote->getPayment()->getAdditionalData();
				
				if($parcelas > 1){
					$transorig = '08';								

				}elseif($parcelas == 1){
					Mage::getStoreConfig('payment/redecard/parcela_sem_juros');
					$transorig = '04';	
				}


				// Monta a variável com os dados para postagem

				$request = 'DATA=' . date("Ymd");
				$request .= '&TRANSACAO=203';
				$request .= '&TRANSORIG=' . $transorig;
				$request .= '&PARCELAS=' . $parcelas;

				$request .= '&FILIACAO=' . Mage::getStoreConfig('payment/redecard/shop_id');
				$request .= '&TOTAL=' . Mage::helper('conamore_gpl')->formataValorCielo($order->getGrandTotal());
				$request .= '&NUMPEDIDO=' . $orderId;
				$request .= '&NUMAUTOR=' . $this->getRequest()->getParam('NUMAUTOR');
				$request .= '&NUMCV=' . $this->getRequest()->getParam('NUMCV');

				$request .= '&NUMSQN=' . $this->getRequest()->getParam('NUMSQN');


				//faco o post para a redecard para confirmar a transação
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'http://ecommerce.redecard.com.br/pos_virtual/confirma.asp');
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				curl_close($ch);
			
			}
		}
		Mage::log("-----------------------------------------------------------Retornou RedeCard-----------------------------------------------------------");

	}


	private function validaRetorno(){
		$codigo_retorno = $this->getRequest()->getParam('CODRET');
		$msg_retorno = $this->getRequest()->getParam('MSGRET');
		//se tem cod_retorno e ele e maior que 49 
		//então deu erro
		if( ($codigo_retorno) && ($codigo_retorno > 49 ) ){
				return false;
		}
		
		return true;
	}

	public function dadoscartaoAction()	{


/*
	Mage::log("Opa dadoscartaoAction ");
		$this->loadLayout();

		$block = $this->getLayout()->createBlock(
		'Mage_Core_Block_Template',
		'Conamore_Gpl_Block_Form_Dadoscartao',
		array('template' => 'gpl/form/frm_dados.phtml')
		);

		$this->getLayout()->getBlock('content')->append($block);

 $this->getResponse()->setBody($this->getLayout()->createBlock('paypal/standard_redirect')->toHtml());

		$this->renderLayout();
$html = "
Aqui vem o texto
";

$this->loadLayout();
    $this->getLayout()->getBlock('root')->setTemplate('gpl/form/frm_dados.phtml');
    $this->renderLayout();

$this->loadLayout();
$this->getResponse()->setBody($this->getLayout()->getBlock('root')->setTemplate('gpl/form/frm_dados.phtml'));
$this->renderLayout();



		$order = new Mage_Sales_Model_Order();
		$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order->loadByIncrementId($orderId);
$quote = Mage::getModel('sales/quote')->load(Mage::getSingleton('checkout/session')->getLastQuoteId());
Mage::log("------------------------pego do banco----------------------------------------------");
Mage::log(Mage::getSingleton('checkout/session')->debug());
Mage::log("------------------------pego do banco----------------------------------------------");
Mage::log("------------------------ppayment----------------------------------------------");
Mage::log($quote->getPayment()->debug());
Mage::log("------------------------ppayment----------------------------------------------");
exit;
*/



//$this->getResponse()->setHeader("Content-Type", "text/html; charset=ISO-8859-1", true);
//$this->getResponse()->setBody($html);

$this->loadLayout();
$block = $this->getLayout()->createBlock('Conamore_Gpl_Block_Form_Dadoscartao','formCartao');
//$block = $this->getLayout()->createBlock('Conamore_Gpl_Block_Form_Dadoscartao','formCartao',array('template' => 'gpl/form/frm_dados.phtml'));
$blockR = $this->getLayout()->createBlock('Mage_Core_Block_Template','infoCompra',array('template' => 'gpl/info/dados_compra.phtml'));
$this->getLayout()->getBlock('content')->append($block);
$this->getLayout()->getBlock('head')->setTitle("Vai misera");
//$this->getLayout()->getBlock('right')->insert($blockR, 'catalog.compare.sidebar', true);
$this->getLayout()->getBlock('right')->append($blockR);
$this->renderLayout();

	}



}

<?php

class Conamore_Gpl_GplController extends Mage_Core_Controller_Front_Action {

	public function testeAction(){
/*
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$codigo = 'status_transacao';
$config = array(
                'position' => 1,
                'required'=> 0,
                'label' => 'Status Da Transação',
                'type' => 'varchar',
                'input'=>'text',
                'apply_to'=>'simple,bundle,grouped,configurable',
                'note'=>'Status da transação retornado pelo gateway para validar a compra compra realizada se for 6 ou 4!'
            );

$setup->addAttribute('order', $codigo , $config);
*/

	}

	public function retornoAction(){
		$order = new Mage_Sales_Model_Order();
		$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order->loadByIncrementId($orderId);

		if($order){
//			$collection = Mage::getModel('sales/order')->getCollection();
//			$collection->addAttributeToSelect('*');
//			$collection->addAttributeToFilter('gateway_tid', $this->getRequest()->getParam('tid'));
//			$collection->load();
			//$item = $collection->getItems();
			//Mage::log($item);
//			$order = $collection->getData();

			$xmlRetorno = $this->_consultaTransacao($order->getGatewayTid());
			$arrayRetorno = $this->_processXmlRetorno($xmlRetorno);
			Mage::log($arrayRetorno);
			if($arrayRetorno['transacao']['status'] == 6 || $arrayRetorno['transacao']['status'] == 4 ){//6 compra realizada com sucesso
				//COMPRA REALIZADA COM SUCESSO
				if ($order->getId()) {
					$this->_createInvoice($order);
					try {
						$order->sendNewOrderEmail();
					} catch (Exception $e) {
						Mage::log("ERROR Arquivo: Conamore_Gpl_GplController linha 39");
						Mage::log("ERRORMSG Nao foi possivel enviar email NewOrderEmail para essa order: ".$order->getIncrementId());
						Mage::log($e->getMessage());
					}
					$order->setData('status_transacao',$arrayRetorno["transacao"]["status"]);
					Mage::log("-------------------Venda-------------------------");
					Mage::log($arrayRetorno["transacao"]["status"]);
					Mage::log($order->getIncrementId());
					Mage::log("-------------------Venda-------------------------");
					$order->setEmailSent(true);
					$order->save();
				}


				Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));
				Mage::app()->getResponse()->sendResponse();

			}else{
			   $objErro = array("erro" => array(
													"codigo" => $arrayRetorno['transacao']['status'],
													"mensagem" => $arrayRetorno['transacao']['autorizacao']['mensagem']
												));
				//cancelo o pedido porque nao foi aprovado o pagamento
		      $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, 'canceled', 'Compra cancelada devido a não autorização do cartão.', false);
				$order->save();

				Mage::getSingleton('core/session')->setErroCompra($objErro);
				Mage::app()->getResponse()->setRedirect(Mage::getUrl("gpl/gpl/falha"));
				Mage::app()->getResponse()->sendResponse();

			}
		}else{
			$objErro = array("erro" => array(
												"codigo" => 00764,
												"mensagem" => 'Tid não encontrado!'
											));

			Mage::getSingleton('core/session')->setErroCompra($objErro);
			Mage::app()->getResponse()->setRedirect(Mage::getUrl("gpl/gpl/falha"));
			Mage::app()->getResponse()->sendResponse();
		}


	}

	public function sucessoAction(){
		$order = new Mage_Sales_Model_Order();
		$xmlTransacao = $this->getRequest()->getParam('transacao');		
		$order->loadByIncrementId($xmlTransacao['dados-pedido']['numero']);
		$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
		
		if ($order->getId()) {
		  try {
				$order->sendNewOrderEmail();
		  } catch (Exception $e) {
				Mage::log($e->getMessage());
		  }
		}

		$order->setEmailSent(true);
		$this->_createInvoice($order);
//      $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, 'complete', '', false);
		$order->save();
//'checkout/onepage/success'
		$this->loadLayout();

		$block = $this->getLayout()->createBlock('Conamore_Gpl_Block_Form_Sucesso','sucesso');
//		$blockR = $this->getLayout()->createBlock('Mage_Core_Block_Template','infoCompra',array('template' => 'gpl/info/dados_compra.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
		$this->getLayout()->getBlock('head')->setTitle("Compra realizada com Sucesso");
		//$this->getLayout()->getBlock('right')->insert($blockR, 'catalog.compare.sidebar', true);
	//	$this->getLayout()->getBlock('right')->append($blockR);
		$this->renderLayout();

	}

	public function falhaAction(){
//$this->_redirect('checkout/onepage/success');
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('Conamore_Gpl_Block_Falha','falha');
//		$blockR = $this->getLayout()->createBlock('Mage_Core_Block_Template','infoCompra',array('template' => 'gpl/info/dados_compra.phtml'));

		//passando parametro para o block
		$block->setData('erro', Mage::getSingleton('core/session')->getErroCompra());
//		$this->getLayout()->getBlock('falha')->setData('erro', $this->getRequest()->getParam('erro'));
//exit;
		$this->getLayout()->getBlock('content')->append($block);
		$this->getLayout()->getBlock('head')->setTitle("Um erro ocorreu em sua compra!");
		//$this->getLayout()->getBlock('right')->insert($blockR, 'catalog.compare.sidebar', true);
	//	$this->getLayout()->getBlock('right')->append($blockR);
		$this->renderLayout();		


	}

	public function buypagelojaAction(){
		$order = new Mage_Sales_Model_Order();
		$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order->loadByIncrementId($orderId);
		$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
	//	Mage::log($quote->debug());
//		$order->sendNewOrderEmail();

		//FAZENDO O POST PARA O GATEWAY
		$retornoXml = $this->_processCielo($order, $quote);
		//VEJO SE RETORNOU O XML
//		Mage::log($retornoXml);
		if($retornoXml){
			$arrayRetorno = $this->_processXmlRetorno($retornoXml);
			if($arrayRetorno['erro']!= null){
				$this->getRequest()->setParams($arrayRetorno);
				//Mage::app()->getResponse()->setRedirect(Mage::getUrl("gpl/gpl/falha", array('extra'=>'params', 'go'=>'here'))); REDIRECIONA COM VARIAVEIS VIA GET
				Mage::app()->getResponse()->setRedirect(Mage::getUrl("gpl/gpl/falha"));
				Mage::app()->getResponse()->sendResponse();
			}else{
				if($arrayRetorno['transacao']['status'] == 6 ){//6
					//COMPRA REALIZADA COM SUCESSO
					$this->_createInvoice($order);
					if ($order->getId()) {
					  try {
							$order->sendNewOrderEmail();
					  } catch (Exception $e) {
							Mage::log("ERROR Arquivo: Conamore_Gpl_GplController linha 96");
							Mage::log("ERRORMSG Nao foi possivel enviar email NewOrderEmail para essa order: ".$order->getIncrementId());
							Mage::log($e->getMessage());
					  }
					}
					$order->setEmailSent(true);

					$order->save();

					Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));
					Mage::app()->getResponse()->sendResponse();

				}else{

					$objErro = array("erro" => array(
														"codigo" => $arrayRetorno['transacao']['status'],
														"mensagem" => $arrayRetorno['transacao']['autorizacao']['mensagem']
													));
					//cancelo o pedido porque nao foi aprovado o pagamento
			      $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, 'canceled', 'Compra cancelada devido a não autorização do cartão.', false);
					$order->save();

					Mage::getSingleton('core/session')->setErroCompra($objErro);
					Mage::app()->getResponse()->setRedirect(Mage::getUrl("gpl/gpl/falha"));
					Mage::app()->getResponse()->sendResponse();

				}

			}
		}

	}


	public function buypagecieloAction(){

		//Primeiro irei registrar transacao
		$xmlRetorno = "";
		$xmlRetorno = $this->_registraTransacao();
		Mage::log($xmlRetorno);
		$arrayRetorno = $this->_processXmlRetorno($xmlRetorno);
			Mage::log($arrayRetorno);
		if($arrayRetorno['erro']!= null){
			Mage::log($arrayRetorno);
			Mage::log('entrou');
			$this->getRequest()->setParams($arrayRetorno);
			Mage::app()->getResponse()->setRedirect(Mage::getUrl("gpl/gpl/falha"));
			Mage::app()->getResponse()->sendResponse();
		}else{
			if($arrayRetorno["transacao"]["url-autenticacao"]!=""){
				$order = new Mage_Sales_Model_Order();
				$orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
				$order->loadByIncrementId($orderId);
				$order->setData('gateway_tid',$arrayRetorno["transacao"]["tid"]);
				//$order->setGatewayTid($arrayRetorno["transacao"]["tid"]);
				$order->save();
				$this->_redirectUrl($arrayRetorno["transacao"]["url-autenticacao"]);
			}
		}

	}

	private function _processCielo($order, $quote){

		 // Dados obtidos da loja para a transação
		 $customer = $order->getCustomer();
		 $payment = $quote->getPayment();

		 // - dados do processo
		 $identificacao = Mage::getStoreConfig('payment/gpl/shop_id');
		 $modulo = 'CIELO';
		 $operacao = 'Autorizacao-Direta';
		 $ambiente = Mage::getStoreConfig('payment/gpl/ambiente_tipo');

		 // - dados do cartão
		 $nome_portador_cartao = $payment->getCcOwner();
		 $numero_cartao = $payment->getCcNumber();
		 $validade_cartao = $payment->getCcExpYear().$payment->getCcExpMonth();
		 $indicador_cartao = $this->_getIdicadorCartao();
		 $codigo_seguranca_cartao = $payment->getCcCid();

		 // - dados do pedido
		 $idioma = Mage::getStoreConfig('payment/gpl/idioma');
		 $valor = Mage::helper('conamore_gpl')->formataValorCielo($order->getGrandTotal()) ;
		 $pedido = $order->getIncrementId();
		 $descricao = '';

		 // - dados do pagamento
		 $bandeira = Mage::helper('conamore_gpl')->getBandeiraCartao($payment->getCcType());
		 $forma_pagamento = $this->_getFormaPagamento($payment->getAdditionalData());
		 $parcelas = $payment->getAdditionalData();
//		 $autorizar = '';
		 $capturar = 'true';

		 // - dados adicionais
		 $campo_livre = '';


		 // Monta a variável com os dados para postagem
		 $request = 'identificacao=' . $identificacao;
		 $request .= '&modulo=' . $modulo;
		 $request .= '&operacao='. $operacao;
		 $request .= '&ambiente=' . $ambiente;

		 $request .= '&nome_portador_cartao=' . $nome_portador_cartao;
		 $request .= '&numero_cartao=' . $numero_cartao;
		 $request .= '&validade_cartao=' . $validade_cartao;
		 $request .= '&indicador_cartao=' . $indicador_cartao;
		 $request .= '&codigo_seguranca_cartao=' . $codigo_seguranca_cartao;

		 $request .= '&idioma=' . $idioma;
		 $request .= '&valor=' . $valor;
		 $request .= '&pedido=' . $pedido;
		 $request .= '&descricao=' . $descricao;

		 $request .= '&bandeira=' . $bandeira;
		 $request .= '&forma_pagamento=' . $forma_pagamento;
		 $request .= '&parcelas=' . $parcelas;
//		 $request .= '&autorizar=' . $autorizar;
		 $request .= '&capturar=' . $capturar;

		 $request .= '&campo_livre=' . $campo_livre;

		 // Faz a postagem para a Cielo
		 $ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, 'https://comercio.locaweb.com.br/comercio.comp');
		 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		 curl_setopt($ch, CURLOPT_POST, true);
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 $response = curl_exec($ch);
		 curl_close($ch);

		
		 return $response;
	}


	private function _processXmlRetorno($XMLtransacao){
		$objXml = array("transacao" => array(
											"tid" => "",
											"pan" => "",
											"dados-pedido" => array("numero" => "", "valor" => "", "moeda" => "", "data-hora" => "", "descricao" => "", "idioma" => ""),
											"forma-pagamento" => array("bandeira" => "", "produto" => "", "parcelas" => ""),
											"status" => "",
											"autenticacao" => array("codigo" => "", "mensagem" => "", "data-hora" => "", "valor" => "", "eci" => ""),
											"autorizacao" => array("codigo" => "", "mensagem" => "", "data-hora" => "", "valor" => "", "lr" => "", "arp" => ""),
											"captura" => array("codigo" => "", "mensagem" => "", "data-hora" => "", "valor" => ""),
											"cancelamento" => array("codigo" => "", "mensagem" => "", "data-hora" => "", "valor" => ""),
											"url-autenticacao" => ""
										));
		$objXmlErro = array("erro" => array(
											"codigo" => "",
											"mensagem" => ""
										));



		$objDom = new DomDocument();
		$loadDom = $objDom->loadXML($XMLtransacao);

		$nodeErro = $objDom->getElementsByTagName('erro')->item(0);
		if ($nodeErro != '') {
			 $nodeCodigoErro = $nodeErro->getElementsByTagName('codigo')->item(0);
			 $objXmlErro['erro']['codigo'] = $nodeCodigoErro->nodeValue;

			 $nodeMensagemErro = $nodeErro->getElementsByTagName('mensagem')->item(0);
			 $objXmlErro['erro']['mensagem'] = $nodeMensagemErro->nodeValue;
			 return $objXmlErro;
			 //Mage::throwException($retorno_mensagem_erro);
		}

		$nodeTransacao = $objDom->getElementsByTagName('transacao')->item(0);
		if ($nodeTransacao != '') {
			 $nodeTID = $nodeTransacao->getElementsByTagName('tid')->item(0);
			 $objXml['transacao']['tid'] = $nodeTID->nodeValue;

			 $nodePAN = $nodeTransacao->getElementsByTagName('pan')->item(0);
			 if($nodePAN !=''){
				 $objXml['transacao']['pan'] = $nodePAN->nodeValue;
			 }


			 $nodeDadosPedido = $nodeTransacao->getElementsByTagName('dados-pedido')->item(0);
			 if ($nodeDadosPedido != '') {
				  $nodeNumero = $nodeDadosPedido->getElementsByTagName('numero')->item(0);
				   $objXml['transacao']['dados-pedido']['numero'] = $nodeNumero->nodeValue;

				  $nodeValor = $nodeDadosPedido->getElementsByTagName('valor')->item(0);
				  $objXml['transacao']['dados-pedido']['valor'] = $nodeValor->nodeValue;

				  $nodeMoeda = $nodeDadosPedido->getElementsByTagName('moeda')->item(0);
				  $objXml['transacao']['dados-pedido']['moeda'] = $nodeMoeda->nodeValue;

				  $nodeDataHora = $nodeDadosPedido->getElementsByTagName('data-hora')->item(0);
				  $objXml['transacao']['dados-pedido']['data-hora'] = $nodeDataHora->nodeValue;

				  $nodeDescricao = $nodeDadosPedido->getElementsByTagName('descricao')->item(0);
				  $objXml['transacao']['dados-pedido']['descricao'] = $nodeDescricao->nodeValue; 

				  $nodeIdioma = $nodeDadosPedido->getElementsByTagName('idioma')->item(0);
				  $objXml['transacao']['dados-pedido']['idioma'] =  $nodeIdioma->nodeValue;
			 }

			 $nodeFormaPagamento = $nodeTransacao->getElementsByTagName('forma-pagamento')->item(0);
			 if ($nodeFormaPagamento != '') {
				  $nodeBandeira = $nodeFormaPagamento->getElementsByTagName('bandeira')->item(0);
				  $objXml['transacao']['forma-pagamento']['bandeira'] = $nodeBandeira->nodeValue;

				  $nodeProduto = $nodeFormaPagamento->getElementsByTagName('produto')->item(0);
				  $objXml['transacao']['forma-pagamento']['produto'] = $nodeProduto->nodeValue; 

				  $nodeParcelas = $nodeFormaPagamento->getElementsByTagName('parcelas')->item(0);
				  $objXml['transacao']['forma-pagamento']['parcelas'] = $nodeParcelas->nodeValue;
			 }

			 $nodeStatus = $nodeTransacao->getElementsByTagName('status')->item(0);
			 $objXml['transacao']['status'] =  $nodeStatus->nodeValue;


			 $nodeAutenticacao = $nodeTransacao->getElementsByTagName('autenticacao')->item(0);
			 if ($nodeAutenticacao != '') {
				  $nodeCodigoAutenticacao = $nodeAutenticacao->getElementsByTagName('codigo')->item(0);
				  $objXml['transacao']['autenticacao']['codigo'] = $nodeCodigoAutenticacao->nodeValue;

				  $nodeMensagemAutenticacao = $nodeAutenticacao->getElementsByTagName('mensagem')->item(0);
				  $objXml['transacao']['autenticacao']['mensagem'] = $nodeMensagemAutenticacao->nodeValue;

				  $nodeDataHoraAutenticacao = $nodeAutenticacao->getElementsByTagName('data-hora')->item(0);
				  $objXml['transacao']['autenticacao']['data-hora'] = $nodeDataHoraAutenticacao->nodeValue;

				  $nodeValorAutenticacao = $nodeAutenticacao->getElementsByTagName('valor')->item(0);
				  $objXml['transacao']['autenticacao']['valor'] = $nodeValorAutenticacao->nodeValue;

				  $nodeECIAutenticacao = $nodeAutenticacao->getElementsByTagName('eci')->item(0);
				  $objXml['transacao']['autenticacao']['eci'] = $nodeECIAutenticacao->nodeValue;
			 }

			 $nodeAutorizacao = $nodeTransacao->getElementsByTagName('autorizacao')->item(0);
			 if ($nodeAutorizacao != '') {
				  $nodeCodigoAutorizacao = $nodeAutorizacao->getElementsByTagName('codigo')->item(0);
				  $objXml['transacao']['autorizacao']['codigo'] = $nodeCodigoAutorizacao->nodeValue;

				  $nodeMensagemAutorizacao = $nodeAutorizacao->getElementsByTagName('mensagem')->item(0);
				  $objXml['transacao']['autorizacao']['mensagem'] = $nodeMensagemAutorizacao->nodeValue;

				  $nodeDataHoraAutorizacao = $nodeAutorizacao->getElementsByTagName('data-hora')->item(0);
				  $objXml['transacao']['autorizacao']['data-hora'] = $nodeDataHoraAutorizacao->nodeValue;

				  $nodeValorAutorizacao = $nodeAutorizacao->getElementsByTagName('valor')->item(0);
				  $objXml['transacao']['autorizacao']['valor'] = $nodeValorAutorizacao->nodeValue;

				  $nodeLRAutorizacao = $nodeAutorizacao->getElementsByTagName('lr')->item(0);
				  $objXml['transacao']['autorizacao']['lr'] = $nodeLRAutorizacao->nodeValue;

				  $nodeARPAutorizacao = $nodeAutorizacao->getElementsByTagName('arp')->item(0);
				  if($nodeARPAutorizacao!=''){
					  $objXml['transacao']['autorizacao']['arp'] = $nodeARPAutorizacao->nodeValue;
				  }

			 }

			 $nodeCaptura = $nodeTransacao->getElementsByTagName('captura')->item(0);
			 if ($nodeCaptura != '') {
			 	  $nodeCodigoCaptura = $nodeCaptura->getElementsByTagName('codigo')->item(0);
				  $objXml['transacao']['captura']['codigo'] = $nodeCodigoCaptura->nodeValue;

				  $nodeMensagemCaptura = $nodeAutorizacao->getElementsByTagName('mensagem')->item(0);
				  $objXml['transacao']['captura']['mensagem'] = $nodeMensagemCaptura->nodeValue;

				  $nodeDataHoraCaptura = $nodeAutorizacao->getElementsByTagName('data-hora')->item(0);
				  $objXml['transacao']['captura']['data-hora'] = $nodeDataHoraCaptura->nodeValue;

				  $nodeValorCaptura = $nodeAutorizacao->getElementsByTagName('valor')->item(0);
				  $objXml['transacao']['captura']['valor'] = $nodeValorCaptura->nodeValue;
			 }

			 $nodeCancelamento = $nodeTransacao->getElementsByTagName('cancelamento')->item(0);
			 if ($nodeCancelamento != '') {
			 	  $nodeCodigoCancelamento = $nodeCancelamento->getElementsByTagName('codigo')->item(0);
				  $objXml['transacao']['cancelamento']['codigo'] = $nodeCodigoCancelamento->nodeValue;

				  $nodeMensagemCancelamento = $nodeAutorizacao->getElementsByTagName('mensagem')->item(0);
				  $objXml['transacao']['cancelamento']['mensagem'] = $nodeMensagemCancelamento->nodeValue;

				  $nodeDataHoraCancelamento = $nodeAutorizacao->getElementsByTagName('data-hora')->item(0);
				  $objXml['transacao']['cancelamento']['data-hora'] = $nodeDataHoraCancelamento->nodeValue;

				  $nodeValorCancelamento = $nodeAutorizacao->getElementsByTagName('valor')->item(0);
				  $objXml['transacao']['cancelamento']['valor'] = $nodeValorCancelamento->nodeValue;
			 }

			 $nodeURLAutenticacao = $nodeTransacao->getElementsByTagName('url-autenticacao')->item(0);
		    if($nodeURLAutenticacao!=''){
			 	$objXml['transacao']['url-autenticacao'] = $nodeURLAutenticacao->nodeValue;
			 }
			 

		}

		return $objXml;
	}

	private function _getFormaPagamento($numParcelas){
		if($numParcelas>1){
			return 2;
		}elseif($numParcelas == 1){
			return 1;
		}

	}

	//caso queira permitir envio com outras opcoes de cid
	private function _getIdicadorCartao(){
		return 1;
	}

	private function _getBandeiraCartao($cc_type){
		switch ($cc_type){
			case "VI":
				return "visa";
				break;
			case "MC":
				return "mastercard";
				break;
			case "AE":
				return "amex";
				break;

		}
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
			Mage::log("ERROR Arquivo: Conamore_Gpl_GplController linha 404");
			Mage::log("ERRORMSG Nao foi possivel criar um invoice para essa order: ".$pOrder->getIncrementId());
			Mage::log($e->getMessage());
		}


	}

	private function _registraTransacao(){

		 $order = new Mage_Sales_Model_Order();
		 $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		 $order->loadByIncrementId($orderId);
		 $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
		 $payment = $quote->getPayment();

		 // - dados do processo
		 $identificacao = Mage::getStoreConfig('payment/gpl/shop_id');
		 $modulo = 'CIELO';
		 $operacao = 'Registro';
		 $ambiente = Mage::getStoreConfig('payment/gpl/ambiente_tipo');

		 // - dados do cartão
		 $bin_cartao = '';

		 // - dados do pedido
		 $idioma = Mage::getStoreConfig('payment/gpl/idioma');//'PT';
		 $valor = Mage::helper('conamore_gpl')->formataValorCielo($order->getGrandTotal()) ;
		 $pedido = $order->getIncrementId();
		 $descricao = '';

		 // - dados do pagamento
		 $bandeira = Mage::helper('conamore_gpl')->getBandeiraCartao($payment->getCcType());
		 $forma_pagamento = $this->_getFormaPagamento($payment->getAdditionalData());
		 $parcelas = $payment->getAdditionalData();
		 $autorizar = '2';//autorizar transacoes autenticadas e nao autenticadas
		 $capturar = 'true';

		 // - dados adicionais
		 $campo_livre = '';


		 // Monta a variável com os dados para postagem
		 $request = 'identificacao=' . $identificacao;
		 $request .= '&modulo=' . $modulo;
		 $request .= '&operacao=' . $operacao;
		 $request .= '&ambiente=' . $ambiente;

		 $request .= '&bin_cartao=' . $bin_cartao;

		 $request .= '&idioma=' . $idioma;
		 $request .= '&valor=' . $valor;
		 $request .= '&pedido=' . $pedido;
		 $request .= '&descricao=' . $descricao;

		 $request .= '&bandeira=' . $bandeira;
		 $request .= '&forma_pagamento=' . $forma_pagamento;
		 $request .= '&parcelas=' . $parcelas;
		 $request .= '&autorizar=' . $autorizar;
		 $request .= '&capturar=' . $capturar;

		 $request .= '&campo_livre=' . $campo_livre;

		 // Faz a postagem para a Cielo
		 $ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, 'https://comercio.locaweb.com.br/comercio.comp');
		 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		 curl_setopt($ch, CURLOPT_POST, true);
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 $response = curl_exec($ch);
		 curl_close($ch);
		 Mage::log($request);
		 return $response;
	}

	private function _consultaTransacao($pTid){

		 // Dados obtidos da loja para a transação

		 // - dados do processo
		 $identificacao = Mage::getStoreConfig('payment/gpl/shop_id');
		 $modulo = 'CIELO';
		 $operacao = 'Consulta';
		 $ambiente = Mage::getStoreConfig('payment/gpl/ambiente_tipo');

		 // Monta a variável com os dados para postagem
		 $request = 'identificacao=' . $identificacao;
		 $request .= '&modulo=' . $modulo;
		 $request .= '&operacao=' . $operacao;
		 $request .= '&ambiente=' . $ambiente;

		 $request .= '&tid=' . $pTid;

		 // Faz a postagem para a Cielo
		 $ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, 'https://comercio.locaweb.com.br/comercio.comp');
		 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		 curl_setopt($ch, CURLOPT_POST, true);
		 curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 $response = curl_exec($ch);
		 curl_close($ch);

		 return $response;
	}


}

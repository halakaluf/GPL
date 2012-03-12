<?php
class Conamore_Adminhtml_Block_Sales_Order_View_Tab_Info extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{  

	public function getMClearSale(){
		$interfaceMClearSale = "";
		if(Mage::getStoreConfig('payment/clearsale/active')==1){
			$interfaceMClearSale = $this->_getMClearSale();
		}
		
		return $interfaceMClearSale;
	}

	// overwrite the default template so we use our custom template
	public function setTemplate($template)	{
		$this->_template = 'conamore/sales/order/view/tab/info.phtml';
		return $this;
	}


	private function _getMClearSale(){

		// Dados da configuração
		$identificacao  = Mage::getStoreConfig('payment/clearsale/shop_id');
		$modulo = "MCLEARSALE";
		$operacao = "AnaliseRisco";
		$ambiente = Mage::getStoreConfig('payment/clearsale/ambiente_tipo');

		// Abaixo, configuramos o formulário com dados para envio.
		$request = "identificacao=" .$identificacao ;
		$request .= "&modulo=" . $modulo;
		$request .= "&operacao=" . $operacao;
		$request .= "&ambiente=" . $ambiente;
		$request .= "&xml=" . urlencode($this->_geraXML());

		// Postagem dos dados para captura da interface
		$urlLocawebCE = "https://comercio.locaweb.com.br/comercio.comp";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urlLocawebCE);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$retorno = curl_exec($ch);
		curl_close($ch);
//Mage::log("https://comercio.locaweb.com.br/comercio.comp?".$request);
		return "<div style='position:relative;'>".utf8_encode($retorno)."</div>";

	}

	private function _geraXML(){

		$order =	$this->getOrder();
		$EndEntrega = $order->getShippingAddress();
		$EndCobranca = $order->getBillingAddress();
//$quote = Mage::getModel('adminhtml/session_quote')->load($order->getQuoteId());
//$quote =  Mage::getSingleton('adminhtml/session_quote')->getData();
		Mage::log($EndEntrega->debug());
		$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		$helper = Mage::helper('conamore_gpl');


		// Dados do pedido
		$pedidoNumero = $order->getIncrementId(); // Número do pedido (sem formatação)
		$arrData = $helper->date_parse_from_format("yyyy-mm-dd HH:ii:ss",$order->getCreatedAt());
		$pedidoData = $arrData["day"]."-".$arrData["month"]."-".$arrData["year"]." ".$arrData["hour"].":".$arrData["minute"].":".$arrData["second"]; // Data do pedido (formato DD-MM-AAAA HH:mm:ss)
		$pedidoIp = $order->getRemoteIp(); // IP utilizado para efetuar o pedido
		$pedidoValorTotal = $helper->formataValorCielo($order->getGrandTotal()); // Total do pedido (sem formatação)
		if($order->getPayment()->getAdditionalData()!=""){
			$parcelas = $order->getPayment()->getAdditionalData();
		}else{
			$parcelas = 1;
		}
		$pedidoParcelas = $parcelas; // Número de parcelas do pedido (sem formatação)

		// Dados de pagamento
		$pagamentoTipo = $this->_getCodeTipoPagamento($order->getPayment()->getMethod()); // Tipo de pagamento do pedido
		$pagamentoBandeiraCartao ="";
		if($pagamentoTipo==1){
			$pagamentoBandeiraCartao = $this->_getCodeBandeiraCartao($order->getPayment()->getCcType()); // Bandeira do Cartão, caso o Tipo de pagamento for Cartão de Crédito
		}


		// Itens do pedido
		// OBS: Para cada item do pedido o nó Item deverá ser replicado com seus respectivos nós e valores.

		$pedidoItensItemCategoria = ""; 
		$items = $order->getAllItems();                  
		$XMLItens = "";
		foreach ($items as $item) {

			$XMLItens .= '<Item>';
			$XMLItens .= '<Codigo>'. $item->getItemId() .'</Codigo>';// Código do produto
			$XMLItens .= '<Descricao>'. $item->getName() .'</Descricao>';// Descrição do produto
			if ($pedidoItensItemCategoria != '') $XMLItens .= '<Categoria>'. $pedidoItensItemCategoria .'</Categoria>';// Categoria do produto
			$XMLItens .= '<Quantidade>'. round($item->getQtyOrdered()) .'</Quantidade>';// Quantidade do produto qty_invoiced
			$XMLItens .= '<ValorUnitario>'. $helper->formataValorCielo($item->getPrice()) .'</ValorUnitario>';// Valor unitário do produto (sem formatação)
			$XMLItens .= '</Item>';

		} 



		// Dados de cobrança
		$regionCobrancaModel = Mage::getModel('directory/region')->load($EndEntrega->getRegionId());
		$cobrancaNome = $EndCobranca->getFirstname()." ".$EndCobranca->getLastname(); // Nome
		$cobrancaEmail = $EndCobranca->getEmail(); // E-mail
		$cobrancaDocumento = $customer->getCustomcpf(); // CPF (sem formatação)
		$cobrancaEndereco = $EndCobranca->getStreet(1); // Endereço
		$cobrancaNumero = ($EndCobranca->getNumero(0)!= "") ? $EndCobranca->getNumero(0) : 0; // Número
		$cobrancaComplemento = ""; // Complemento
		$cobrancaBairro = $EndCobranca->getBairro(); // Bairro
		$cobrancaCidade = $EndCobranca->getCity(); // Cidade
		$cobrancaCep = $EndCobranca->getPostcode(); // CEP
		$cobrancaEstado = $regionCobrancaModel->getCode(); // Estado (sigla)
		$cobrancaPais = $EndCobranca->getCountryId(); // País (sigla)
		//$EndCobranca->getTelephone() 
		//(21) 8812-37581 
		$dddTel = str_replace("(", "", $EndCobranca->getTelephone());
		$dddTel = str_replace(")", "", $dddTel);
		$dddTel = str_replace("-", "", $dddTel);
		$arrDDDTel = split(" ", $dddTel);
//Mage::log($dddTel);
//Mage::log($arrDDDTel);
//Mage::log($EndCobranca->getStreet());
//Mage::log($EndCobranca->getStreet(0));
//Mage::log($EndCobranca->getStreet(1));

		$cobrancaDddTelefone = ($arrDDDTel[0]!= "") ? $arrDDDTel[0] : 11; // DDD Telefone
		$cobrancaTelefone = $arrDDDTel[1]; // Telefone
		$cobrancaDddCelular = ""; // DDD Celular
		$cobrancaCelular = ""; // Celular

		// Dados de entrega
		$regionModel = Mage::getModel('directory/region')->load($EndEntrega->getRegionId());

		$entregaNome = $EndEntrega->getFirstname()." ".$EndEntrega->getLastname(); // Nome
		$entregaEmail = $EndEntrega->getEmail(); // E-mail
		$entregaDocumento = $customer->getCustomcpf(); // CPF (sem formatação)
		$entregaEndereco = $EndEntrega->getStreet(1); // Endereço do pedido
		$entregaNumero = ($EndEntrega->getNumero(0)!= "") ? $EndEntrega->getNumero(0) : 0; // Número
		$entregaComplemento = ""; // Complemento
		$entregaBairro = $EndEntrega->getBairro(); // Bairro
		$entregaCidade = $EndEntrega->getCity(); // Cidade
		$entregaCep = $EndEntrega->getPostcode(); // CEP
		$entregaEstado = $regionModel->getCode(); // Estado (sigla)
		$entregaPais = $EndEntrega->getCountryId(); // País (sigla)
		$dddTel = str_replace("(", "", $EndEntrega->getTelephone());
		$dddTel = str_replace(")", "", $dddTel);
		$dddTel = str_replace("-", "", $dddTel);
		$arrDDDTel = split(" ", $dddTel);
		$entregaDddTelefone = ($arrDDDTel[0]!= "") ? $arrDDDTel[0] : 11; // DDD Telefone
		$entregaTelefone = $arrDDDTel[1]; // Telefone
		$entregaDddCelular = ""; // DDD Celular
		$entregaCelular = ""; // Celular

		// ############# Inicio da Montagem do XML da consulta #############
		$xmlRequisicao = '<?xml version="1.0" encoding="utf-8" ?>';
		$xmlRequisicao .= '<Locaweb>';
			 $xmlRequisicao .= '<Pedido>';
				  $xmlRequisicao .= '<Numero>'. $pedidoNumero .'</Numero>';
				  $xmlRequisicao .= '<Data>'. $pedidoData .'</Data>';
				  if ($pedidoIp != '') $xmlRequisicao .= '<Ip>'. $pedidoIp .'</Ip>';
				  $xmlRequisicao .= '<ValorTotal>'. $pedidoValorTotal .'</ValorTotal>';
				  $xmlRequisicao .= '<Parcelas>'. $pedidoParcelas .'</Parcelas>';
				  $xmlRequisicao .= '<Pagamento>';
				      $xmlRequisicao .= '<Tipo>'. $pagamentoTipo .'</Tipo>';
				      if ($pagamentoBandeiraCartao != '') $xmlRequisicao .= '<BandeiraCartao>'. $pagamentoBandeiraCartao .'</BandeiraCartao>';
				  $xmlRequisicao .= '</Pagamento>';
				  $xmlRequisicao .= '<Itens>';

				      $xmlRequisicao .= $XMLItens;

				  $xmlRequisicao .= '</Itens>';
				  $xmlRequisicao .= '<Cobranca>';
				      $xmlRequisicao .= '<Nome>'. $cobrancaNome .'</Nome>';
				      $xmlRequisicao .= '<Email>'. $cobrancaEmail .'</Email>';
				      $xmlRequisicao .= '<Documento>'. $cobrancaDocumento .'</Documento>';
				      $xmlRequisicao .= '<Endereco>'. $cobrancaEndereco .'</Endereco>';
				      $xmlRequisicao .= '<Numero>'. $cobrancaNumero .'</Numero>';
				      if ($cobrancaComplemento != '') $xmlRequisicao .= '<Complemento>'. $cobrancaComplemento .'</Complemento>';
				      $xmlRequisicao .= '<Bairro>'. $cobrancaBairro .'</Bairro>';
				      $xmlRequisicao .= '<Cidade>'. $cobrancaCidade .'</Cidade>';
				      $xmlRequisicao .= '<Cep>'. $cobrancaCep .'</Cep>';
				      $xmlRequisicao .= '<Estado>'. $cobrancaEstado .'</Estado>';
				      $xmlRequisicao .= '<Pais>'. $cobrancaPais .'</Pais>';
				      $xmlRequisicao .= '<DddTelefone>'. $cobrancaDddTelefone .'</DddTelefone>';
				      $xmlRequisicao .= '<Telefone>'. $cobrancaTelefone .'</Telefone>';
				      if ($cobrancaDddCelular != '') $xmlRequisicao .= '<DddCelular>'. $cobrancaDddCelular .'</DddCelular>';
				      if ($cobrancaCelular != '') $xmlRequisicao .= '<Celular>'. $cobrancaCelular .'</Celular>';
				  $xmlRequisicao .= '</Cobranca>';
				  $xmlRequisicao .= '<Entrega>';
				      $xmlRequisicao .= '<Nome>'. $entregaNome .'</Nome>';
				      $xmlRequisicao .= '<Email>'. $entregaEmail .'</Email>';
				      $xmlRequisicao .= '<Documento>'. $entregaDocumento .'</Documento>';
				      $xmlRequisicao .= '<Endereco>'. $entregaEndereco .'</Endereco>';
				      $xmlRequisicao .= '<Numero>'. $entregaNumero .'</Numero>';
				      if ($entregaComplemento != '') $xmlRequisicao .= '<Complemento>'. $entregaComplemento .'</Complemento>';
				      $xmlRequisicao .= '<Bairro>'. $entregaBairro .'</Bairro>';
				      $xmlRequisicao .= '<Cidade>'. $entregaCidade .'</Cidade>';
				      $xmlRequisicao .= '<Cep>'. $entregaCep .'</Cep>';
				      $xmlRequisicao .= '<Estado>'. $entregaEstado .'</Estado>';
				      $xmlRequisicao .= '<Pais>'. $entregaPais .'</Pais>';
				      $xmlRequisicao .= '<DddTelefone>'. $entregaDddTelefone .'</DddTelefone>';
				      $xmlRequisicao .= '<Telefone>'. $entregaTelefone .'</Telefone>';
				      if ($entregaDddCelular != '') $xmlRequisicao .= '<DddCelular>'. $entregaDddCelular .'</DddCelular>';
				      if ($entregaCelular != '') $xmlRequisicao .= '<Celular>'. $entregaCelular .'</Celular>';
				  $xmlRequisicao .= '</Entrega>';
			 $xmlRequisicao .= '</Pedido>';
		$xmlRequisicao .= '</Locaweb>';
		// ############# Fim da Montagem do XML da consulta #############
		return $xmlRequisicao;

	}

	public function showLinkToBarCode(){
			//Mage::getUrl('cms/page/view', array('id' => 1)); 
		$url =  Mage::getModel('adminhtml/url')->getUrl('gpl/barcode/gerar',array('id' => $this->getOrder()->getIncrementId())); 
		return "<br/><a href='$url' target='_blank'>Imprimir Etiqueta</a>";
	}


	private function _getCodeTipoPagamento($metodoPagamento){
		switch ($metodoPagamento) {
			 case "boletos":
				  return 2;
				  break;
			 case "gpl":
				  return 1;
				  break;
		}

	}

	private function _getCodeBandeiraCartao($bandeira){
		switch ($bandeira) {
			 case "VI":
				  return 3;
				  break;
			 case "MC":
				  return 2;
				  break;
		}

	}

}  

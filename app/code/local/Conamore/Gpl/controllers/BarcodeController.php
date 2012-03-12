<?php
require_once("Barcode/Barcode.php"); // chamada para a biblioteca Image_Barcode

class Conamore_Gpl_BarcodeController extends Mage_Core_Controller_Front_Action {

	public function gerarAction()	{

		$html = '
		<style type="text/css">
		<!--
		.style1 {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
		}
		.style2 {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 10px;
			float: left;
		}
		.style4 {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 10px;
		}
		.style6 {font-family: Arial, Helvetica, sans-serif; font-size: 18px; }
		.style7 {width: 250px;  height: 150px; text-align: center; float:right; font-family: Arial, Helvetica, sans-serif; font-size: 18px; }

		-->;
		</style>
 <SCRIPT LANGUAGE="JavaScript">
window.print();
</SCRIPT> 
		<table cellspacing="0" cellpadding="0" border="0" height="150" align="center" width="765px">
			<tbody>
				<tr>
					<td align="center">
						<table width="100%"><tbody><tr><td align="left" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;<img border="0" src="'.Mage::getBaseUrl('media') . 'gpl/correios/destinatario.gif"></td><td align="right" valign="top"><img border="0" src="'.Mage::getBaseUrl('media') . 'gpl/correios/logo_correios.gif"></td></tr></tbody></table>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<span class="style6">
							&nbsp;&nbsp;&nbsp;&nbsp;%#NOME#%<br>
							&nbsp;&nbsp;&nbsp;&nbsp;%#ENDERECO#%&nbsp;&nbsp;&nbsp;%#NUMERO#%<br>
							%#COMPLEMENTO#%
							&nbsp;&nbsp;&nbsp;&nbsp;%#BAIRRO#%<br>
							&nbsp;&nbsp;&nbsp;&nbsp;%#CIDADE#%/%#ESTADO#%<br>
							&nbsp;&nbsp;&nbsp;&nbsp;%#CEP#%
						</span>
						<div align="center" class="style4">
						<br>
						<img alt="codigo de barras" title="codigo de barras" src="http://www.barcodesinc.com/generator/image.php?code=%#CEPCODEBARRA#%&style=200&type=C128C&width=300&height=150&xres=3&font=4"><br>
						</div>
						<br>
						<hr color="silver" align="center" width="100%" size="1">
						<div class="style2">
							<b>Remetente:</b>
							<br/>
							%#REMETENTE#%
							<!--br> TESTE<br>
							71 32499095<br> RUA NOSSA SENHORA AUXILIADORA&nbsp;&nbsp;40<br>
							ITAPUÃ<br> 41620720&nbsp;&nbsp;&nbsp;SALVADOR/BA-->
						</div>
						<div class="style7"><img alt="Marca Tipo Frete" title="Marca Tipo Frete" src="%#URLTPFRETE#%"></div>
					</td>
				</tr>
			</tbody>
		</table>';


		$order_id  = $this->getRequest()->getParam('id');
		if($order_id != ""){

			$order = new Mage_Sales_Model_Order();
			$order->loadByIncrementId($order_id);
			$EndEntrega = $order->getShippingAddress();
			$metodoEntrega = substr($order->getShippingMethod(),-5);
			$urlLogoMetodoEnvio = "";

			switch($metodoEntrega) {
				 case 40010:
				 case 40096:
				 case 40215:
				 case 40290:
				 case 40045:
					  $urlLogoMetodoEnvio = Mage::getBaseUrl('media') . 'gpl/correios/Chancela_SEDEX1.jpg';
					  break;
				 case 81019:
					  $urlLogoMetodoEnvio = Mage::getBaseUrl('media') . 'gpl/correios/Chancela_eSedex1.jpg';
					  break;
				 case 41106:
				 case 41068:
					  $urlLogoMetodoEnvio = Mage::getBaseUrl('media') . 'gpl/correios/Chancela_PAC1.png.jpg';
					  break;
			}



			$nomeEntraga = $EndEntrega->getFirstname()." ".$EndEntrega->getLastname();
			$entregaNumero = ($EndEntrega->getNumero(0)!= "") ? $EndEntrega->getNumero(0) : "S/N";
			$cepcliente = $EndEntrega->getPostcode();
			$regionCobrancaModel = Mage::getModel('directory/region')->load($EndEntrega->getRegionId());

			$cepk = explode("-",$cepcliente); // explodindo o traço do CEP
			$cepfinal = implode($cepk); // juntando as duas partes sem o traço
			$urlImage =  Mage::getUrl('gpl/barcode/image',array('cep' => $cepfinal)); 
			$html = str_replace("%#NOME#%", $nomeEntraga, $html);
			$html = str_replace("%#CEP#%", $EndEntrega->getPostcode(), $html);
			$html = str_replace("%#ENDERECO#%", $EndEntrega->getStreet(1), $html);
			$html = str_replace("%#NUMERO#%", $entregaNumero, $html);
			$html = str_replace("%#BAIRRO#%", $EndEntrega->getBairro(), $html);
			$html = str_replace("%#CIDADE#%", $EndEntrega->getCity(), $html);
			$html = str_replace("%#ESTADO#%", $regionCobrancaModel->getCode(), $html);
			$html = str_replace("%#URLIMG#%", $urlImage, $html);
			$html = str_replace("%#URLTPFRETE#%", $urlLogoMetodoEnvio, $html);
			$html = str_replace("%#CEPCODEBARRA#%", $cepfinal, $html);

			$ordem   = array("\r\n", "\n", "\r");
			$replace = '<br />';
			// Processes \r\n's first so they aren't converted twice.
			// troco o /r/n por br para exibir na page com a quebra de linha
			$remetente = str_replace($ordem, $replace, Mage::getStoreConfig('sales/identity/address'));
			$html = str_replace("%#REMETENTE#%", $remetente, $html);

			$complemento = "";
			if($EndEntrega->getStreet(2)!=""){
				$complemento = "&nbsp;&nbsp;&nbsp;&nbsp;".$EndEntrega->getStreet(2)."&nbsp;&nbsp;&nbsp;".$EndEntrega->getNumero(1)."<br>";
			}
			$html = str_replace("%#COMPLEMENTO#%", $complemento, $html);
			echo $html;

		}else{
			echo "Pedido não encontrado!";
		}



/*
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
*/
	}


	public function imageAction(){

      header("Content-Type: image/png");
		$cep  = $this->getRequest()->getParam('cep');
		if($cep != ""){
			$type = 'code128'; // tipo de barra gerada
			Image_Barcode::draw($cep, $type); // Imprimindo o código de barras na tela
		}


	}


}

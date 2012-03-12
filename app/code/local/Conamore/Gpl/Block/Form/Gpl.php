<?php

class Conamore_Gpl_Block_Form_Gpl extends Mage_Payment_Block_Form_Cc
{

    /**
     * Internal constructor
     * Set info template for payment step
     *
     */
    protected function _construct(){
         parent::_construct();
			//vejo qual o tipo de contrato para poder instanciar o form certo
			if(Mage::getStoreConfig('payment/gpl/contrato_tipo') == "BUY_PAGE_CIELO"){
	         $this->setTemplate('gpl/form/frm_gpl_buy_page_cielo.phtml');
			}elseif(Mage::getStoreConfig('payment/gpl/contrato_tipo') == "BUY_PAGE_LOJA"){
	         $this->setTemplate('gpl/form/frm_gpl_buy_page_loja.phtml');
			}

    }


	 private function geraParcelas($numParcelas){
		$blocoParcelas ="";
		$totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
		$subtotal = $totals["grand_total"]->getValue();
		setlocale(LC_MONETARY,"pt_BR", "ptb");
		


		if(Mage::getStoreConfig('payment/gpl/valor_minimo_parcelamento') <= $subtotal){

			for ($i = 1; $i <= $numParcelas; $i++){
	
				$selected = "";
				if($i>1){
					$aVistaSemJuros = "Sem Juros";
					$valorParcela = 	$subtotal/$i;
					$prefixVal = $i."x de";
					$classRequerida = "";
					$selected = "checked";
				}else{
					$aVistaSemJuros = "à Vista";
					$valorParcela = $subtotal;
					$prefixVal = "";
					$selected = "checked";
					$classRequerida = "validate-one-required";
				}

				$blocoParcelas .='<p>
											<input type="radio" '.$selected. ' class="inputRadio '.$classRequerida.'" name="payment[num_parcelas]" id="numParcelas-'.$i.'" value="'.$i.'">&nbsp;
											<label for="numParcelas-'.$i.'">'.$prefixVal.' '. money_format('%n', $valorParcela) .' ' . $aVistaSemJuros . '</label>
										</p>';
	
			}


		}else{

				$blocoParcelas ='<p>
											<input type="radio" checked class="inputRadio validate-one-required" name="payment[num_parcelas]" id="numParcelas-1" value="1">&nbsp;
											<label for="numParcelas-1">'. money_format('%n', $subtotal) .' à Vista</label>
										</p>';

		}
		return $blocoParcelas;

	 }

	 public function geraHtmlBandeiras(){

//Mage::getBaseDir('media')
//return Mage::helper('conamore_gpl')->geraHtmlBandeiras();
			$html = '<p><span class="titulo">Escolha o Cartão:</span></p><input type="hidden" id="idOperadora" name="payment[operadora]">';
			$cartoesAceitos = explode(",", Mage::getStoreConfig('payment/gpl/cctypes'));
			$vezesSemJuros = explode(",", Mage::getStoreConfig('payment/gpl/parcela_sem_juros'));
			$blocoForms ="";
			//$html .= ;
			$total = count($cartoesAceitos);
			for ($i = 0; $i < $total; $i++) {
				$nomeCartao = "";
				$cardCode = "";
				switch (strtolower($cartoesAceitos[$i])) {
					 case "ae":
						  $nomeCartao = "American express";
						  $cardCode = "ae";
						  break;
					 case "mc":
						  $cardCode = "mc";
						  $nomeCartao = "Mastercard";
						  break;
					 case "vi":
						  $cardCode = "vi";
						  $nomeCartao = "Visa";
						  break;
				}
				$vezes = "";
				if($vezesSemJuros[$i]>1){
					$vezes = "-".$vezesSemJuros[$i]."x";
				}

				$blocoForms.= '<div class="form-gpl-cartoes" id="form-'. $cardCode.'" style="display:none;">
										<div class="parcelas">'.$this->geraParcelas($vezesSemJuros[$i]). '</div>
										<div class="form-cartao">

											<div class="input-box">
												<label for="_cc_owner" class="required"><em>*</em>'.$this->__('Name on Card') .'</label>
												<input type="text" title="'. $this->__('Name on Card') .'" class="input-text required-entry" id="_cc_owner" name="payment['. $cardCode .'_cc_owner]" value="" />
											</div>

											<label for="cc_number" class="required"><em>*</em>'. $this->__('Credit Card Number').'</label>
											<div class="input-box">
												<input type="text" id="cc_number" name="payment['. $cardCode.'_cc_number]" title="'. $this->__('Credit Card Number').'" class="input-text required-entry validate-number" value="" />
											</div>

											<label for="cc_expiration" class="required"><em>*</em> '. $this->__('Expiration Date').' </label>
											<div class="input-box">
												<div class="v-fix">
													<select id="cc_expiration" name="payment['. $cardCode.'_cc_exp_month]" class="month required-entry">
														<option selected="selected" value="">'.$this->__('Month').'</option>
														<option value="01">01</option>
														<option value="02">02</option>
														<option value="03">03</option>
														<option value="04">04</option>
														<option value="05">05</option>
														<option value="06">06</option>
														<option value="07">07</option>
														<option value="08">08</option>
														<option value="09">09</option>
														<option value="10">10</option>
														<option value="11">11</option>
														<option value="12">12</option>
													</select>
												</div>
												<div class="v-fix">
													<select id="expiration_yr" name="payment['. $cardCode.'_cc_exp_year]" class="year required-entry">
														<option selected="selected" value="">'.$this->__('Year').'</option>
														<option value="2011">2011</option>
														<option value="2012">2012</option>
														<option value="2013">2013</option>
														<option value="2014">2014</option>
														<option value="2015">2015</option>
														<option value="2016">2016</option>
														<option value="2017">2017</option>
														<option value="2018">2018</option>
														<option value="2019">2019</option>
														<option value="2020">2020</option>
														<option value="2021">2021</option>
													</select>
												</div>
											</div>

											<label for="cc_cid" class="required"><em>*</em>'.$this->__('Card Verification Number').'</label>
											<div class="input-box">
												<div class="v-fix">
													 <input type="text" title="'. $this->__('Card Verification Number') .'" class="input-text cvv required-entry validate-number validate-cc-cvn" id="cc_cid" name="payment['. $cardCode.'_cc_cid]" value="" />
												</div>
												<a href="#" class="cvv-what-is-this">'. $this->__('What is this?') . '</a>
											</div>

						
										</div>

										<div class="clear"></div>
								 </div>';


				$html .='<span id="'.$cardCode.'" class="bandeira-container"><img class="bandeira" src="'. Mage::getBaseUrl('media') . 'gpl/bandeiras/bandeira-'.strtolower($cartoesAceitos[$i]).$vezes. '.gif" title="'.$nomeCartao.'"></span>';

			}
			$html .= $blocoForms;
			return $html;
	 }


	 public function geraHtmlBandeirasBuyPageCielo(){

			$html = '<p><span class="titulo">Escolha o Cartão:</span></p><input type="hidden" id="idOperadora" name="payment[operadora]">';
			$cartoesAceitos = explode(",", Mage::getStoreConfig('payment/gpl/cctypes'));
			$vezesSemJuros = explode(",", Mage::getStoreConfig('payment/gpl/parcela_sem_juros'));
			$blocoForms ="";

			$total = count($cartoesAceitos);
			for ($i = 0; $i < $total; $i++) {
				$nomeCartao = "";
				$cardCode = "";
				switch (strtolower($cartoesAceitos[$i])) {
					 case "ae":
						  $nomeCartao = "American express";
						  $cardCode = "ae";
						  break;
					 case "mc":
						  $cardCode = "mc";
						  $nomeCartao = "Mastercard";
						  break;
					 case "vi":
						  $cardCode = "vi";
						  $nomeCartao = "Visa";
						  break;
				}
				$vezes = "";
				if($vezesSemJuros[$i]>1){
					$vezes = "-".$vezesSemJuros[$i]."x";
				}

				$blocoForms.= '<div class="form-gpl-cartoes" id="form-'. $cardCode.'" style="display:none;">
										<div class="parcelas">'.$this->geraParcelas($vezesSemJuros[$i]). '</div>
										<div class="form-cartao" style="width: 400px;">
											<h2>Informação</h2>
											<p>Após o fechamento do pedido você será redirecionado ao ambiente da operadora de Cartão.</p>
										</div>
										<div class="clear"></div>
								 </div>';


				$html .='<span id="'.$cardCode.'" class="bandeira-container"><img class="bandeira" src="'. Mage::getBaseUrl('media') . 'gpl/bandeiras/bandeira-'.strtolower($cartoesAceitos[$i]).$vezes. '.gif" title="'.$nomeCartao.'"></span>';

			}
			$html .= $blocoForms;
			return $html;
	 }

}



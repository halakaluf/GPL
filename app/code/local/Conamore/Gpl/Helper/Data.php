<?php
 
class Conamore_Gpl_Helper_Data extends Mage_Core_Helper_Abstract { 

    /**
     * Get controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return Mage::app()->getFrontController()->getRequest()->getControllerName();
    }

	 public function geraHtmlBandeiras(){
			$html = "Teste func help";
			
			$html .= Mage::getStoreConfig('payment/gpl/cctypes');
			return $html;
	 }

    /**
     * Retrieve list of months translation
     *
     * @return array
     */
    public function getMonths()
    {
        $data = Mage::app()->getLocale()->getTranslationList('month');
        foreach ($data as $key => $value) {
            $monthNum = ($key < 10) ? '0'.$key : $key;
            $data[$key] = $monthNum . ' - ' . $value;
        }
        return $data;
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     */
    public function getYears()
    {
        $years = array();
        $first = date("Y");

        for ($index=0; $index <= 10; $index++) {
            $year = $first + $index;
            $years[$year] = $year;
        }
        return $years;
    }

	/*
	 * Formata o valor do pedido no formato exigido pelo Gateway LocaWeb retirado do modulo I-PAGARE.
	 * Por exemplo, um pedido de
	 * - valor total R$ 499,99 deve ser enviado como 49999
	 * - valor total R$ 499,00 deve ser enviado como 49900
	 * - quantidade igual a 1 deve ser enviado como 100
	 * - quantidade igual a 1,5 deve ser enviado como 150
	 */
	public function formataValorCielo($amount){
		$amountInt = (int)$amount;
		if($amountInt == $amount){
			//se o número for inteiro, então tira os zeros do final.
			$amount = (int)$amount;
			//transforma em string
			$amountStr = $amount;
			$amountStr = str_ireplace(",", "", $amountStr);
			$amountStr = str_ireplace(".", "", $amountStr);
			//adiciona os zeros dos centavos.
			$amountStr = $amountStr  . '00';
		}else{
			$amount = round($amount, 2);
			$amountStr = $amount;
			
			$pos = strpos($amountStr, '.');
			$decimais = substr($amountStr, $pos+1);
			$tamDecimais = strlen($decimais);
			
			while($tamDecimais < 2){
				$amountStr = $amountStr . '0';
				
				$pos = strpos($amountStr, '.');
				$decimais = substr($amountStr, $pos+1);
				$tamDecimais = strlen($decimais);
			}
			
			$amountStr = str_ireplace(",", "", $amountStr);
			$amountStr = str_ireplace(".", "", $amountStr);
		}
		 
		return $amountStr;
	}


	public function getBandeiraCartao($cc_type){
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
	
   //$helper = Mage::helper('conamore_gpl');
   //$helper->date_parse_from_format("yyyy-mm-dd HH:ii:ss",$order->getCreatedAt());
	public function date_parse_from_format($format, $date) {
	  $dMask = array(
		 'H'=>'hour',
		 'i'=>'minute',
		 's'=>'second',
		 'y'=>'year',
		 'm'=>'month',
		 'd'=>'day'
	  );
	  $format = preg_split('//', $format, -1, PREG_SPLIT_NO_EMPTY); 
	  $date = preg_split('//', $date, -1, PREG_SPLIT_NO_EMPTY); 
	  foreach ($date as $k => $v) {
		 if ($dMask[$format[$k]]) $dt[$dMask[$format[$k]]] .= $v;
	  }
	  return $dt;
	}



}

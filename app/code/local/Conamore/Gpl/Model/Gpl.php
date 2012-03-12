 <?php

 /**
 * Our test CC module adapter
 */
// class Conamore_Gpl_Model_Gpl extends Mage_Payment_Model_Method_Cc
 class Conamore_Gpl_Model_Gpl extends Mage_Payment_Model_Method_Abstract {
     /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
     protected $_code = 'gpl';
 
     /**
      * Here are examples of flags that will determine functionality availability
      * of this module to be used by frontend and backend.
      *
      * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
      *
      * It is possible to have a custom dynamic logic by overloading
      * public function can* for each flag respectively
      */
protected $_formBlockType = 'gpl/form_gpl';
protected $_infoBlockType = 'gpl/info_gpl';
     /**
      * Is this payment method a gateway (online auth/charge) ?
      */
     protected $_isGateway               = true;
  
     /**
      * Can authorize online?
      */
     protected $_canAuthorize            = true;
  
     /**
      * Can capture funds online?
      */
     protected $_canCapture              = true;
  
     /**
      * Can capture partial amounts online?
      */
     protected $_canCapturePartial       = false;
  
     /**
      * Can refund online?
      */
     protected $_canRefund               = false;
  
     /**
      * Can void transactions online?
      */
     protected $_canVoid                 = true;
  
     /**
      * Can use this payment method in administration panel?
      */
     protected $_canUseInternal          = true;
  
     /**
      * Can show this payment method as an option on checkout payment page?
      */
     protected $_canUseCheckout          = true;
  
     /**
      * Is this payment method suitable for multi-shipping checkout?
      */
     protected $_canUseForMultishipping  = true;
  
     /**
      * Can save credit card information for future processing?
      */
     protected $_canSaveCc = true;
  



    /**
     * Assign data to info model instance 
	  *	primeiro  a ser chamado na fila
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)    {
//d($data);
//Mage::log("------------------------assignData----------------------------------------------");
//Mage::log($data->debug());
//Mage::throwException(var_dump($data));

        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

      $info = $this->getInfoInstance();
		$info->setAdditionalData($data->getNumParcelas());
		switch ($data->getOperadora()) {
			case "ae":
				$info->setCcType("AE")
					->setCcOwner($data->getAeCcOwner())
					->setCcLast4(substr($this->_removeDelimiters($data->getAeCcNumber()), -4))
					->setCcNumber($this->_removeDelimiters($data->getAeCcNumber()))
					->setCcCid($data->getAeCcCid())
					->setCcExpMonth($data->getAeCcExpMonth())
					->setCcExpYear($data->getAeCcExpYear())
					;
				break;
			case "vi":
				$info->setCcType("VI")
					->setCcOwner($data->getViCcOwner())
					->setCcLast4(substr($this->_removeDelimiters($data->getViCcNumber()), -4))
					->setCcNumber($this->_removeDelimiters($data->getViCcNumber()))
					->setCcCid($data->getViCcCid())
					->setCcExpMonth($data->getViCcExpMonth())
					->setCcExpYear($data->getViCcExpYear())
					;
				break;
			case "mc":
				$info->setCcType("MC")
					->setCcOwner($data->getMcCcOwner())
					->setCcLast4(substr($this->_removeDelimiters($data->getMcCcNumber()), -4))
					->setCcNumber($this->_removeDelimiters($data->getMcCcNumber()))
					->setCcCid($data->getMcCcCid())
					->setCcExpMonth($data->getMcCcExpMonth())
					->setCcExpYear($data->getMcCcExpYear())
					;
			break;
		}

//Mage::log(Mage::app()->getRequest()->getPost());


//Mage::log($info->debug());
        return $this;
    }



    /**
     * Prepare info instance for save
     *
	  *	terceiro  a ser chamado na fila
     * @return Mage_Payment_Model_Abstract
     */
    public function prepareSave()    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc) {
            $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        }
        //$info->setCcCidEnc($info->encrypt($info->getCcCid()));
        $info->setCcNumber(null)
            ->setCcCid(null);
        return $this;
    }



    /**
     * Validate payment method information object
	  *	segundo  a ser chamado na fila
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()    {
		/*
		* calling parent validate function
		*/
		parent::validate();
		$errorMsg = false;
		if(Mage::getStoreConfig('payment/gpl/contrato_tipo') == "BUY_PAGE_CIELO"){
			$errorMsg = $this->validaBuyPageCielo();
		}elseif(Mage::getStoreConfig('payment/gpl/contrato_tipo') == "BUY_PAGE_LOJA"){
			$errorMsg = $this->validaBuyPageLoja();
		}

		if($errorMsg){
			Mage::throwException($errorMsg);
			//throw Mage::exception('Mage_Payment', $errorMsg, $errorCode);
		}
		//This must be after all validation conditions
		if ($this->getIsCentinelValidationEnabled()) {
			$this->getCentinelValidator()->validate($this->getCentinelValidationData());
		}

		return $this;

	 }

	/**
	* validaBuyPageCielo
	*
	* funcao para validar os dados do form do cartao no contrato Buy Page Cielo
	* Nesse modo o cliente vai ser redirecionado para uma pagina da Cielo para coloca os dados do cartao de credito
	* @return  bool
	*/

	private function validaBuyPageCielo(){
   
		$info = $this->getInfoInstance();
		$errorMsg = false;
		$parcelas =  $info->getAdditionalData();

		if ($parcelas == ""){
				 $errorMsg = $this->_getHelper()->__('Numero de parcelas inexistente');
		}
		return $errorMsg;
	}


	/**
	* validaBuyPageLoja
	*
	* funcao para validar os dados do form do cartao no contrato Buy Page Loja
	* Nesse modo o cliente coloca os dados do cartao de credito na loja virtual
	* @return  bool
	*/

	private function validaBuyPageLoja(){
		$errorMsg = false;
		$info = $this->getInfoInstance();
		$availableTypes = explode(',',$this->getConfigData('cctypes'));
		$ccNumber = $info->getCcNumber();

		if (in_array(strtoupper($info->getCcType()), $availableTypes)){

			if ($this->validateCcNum($ccNumber)
				 // Other credit card type number validation
				 || ($this->OtherCcType(strtoupper($info->getCcType())) && $this->validateCcNumOther($ccNumber))) {

				 $ccType = 'OT';
				 $ccTypeRegExpList = array(
					  //Solo, Switch or Maestro. International safe
					  //'SS'  => '/^((6759[0-9]{12})|(6334|6767[0-9]{12})|(6334|6767[0-9]{14,15})|(5018|5020|5038|6304|6759|6761|6763[0-9]{12,19})|(49[013][1356][0-9]{12})|(633[34][0-9]{12})|(633110[0-9]{10})|(564182[0-9]{10}))([0-9]{2,3})?$/', // Maestro / Solo
					  'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/', // Solo only
					  'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
					  'VI'  => '/^4[0-9]{12}([0-9]{3})?$/',             // Visa
					  'MC'  => '/^5[1-5][0-9]{14}$/',                   // Master Card
					  'AE'  => '/^3[47][0-9]{13}$/',                    // American Express
					  'DI'  => '/^6011[0-9]{12}$/',                     // Discovery
					  'JCB' => '/^(3[0-9]{15}|(2131|1800)[0-9]{11})$/', // JCB
				 );

				 foreach ($ccTypeRegExpList as $ccTypeMatch=>$ccTypeRegExp) {
					  if (preg_match($ccTypeRegExp, $ccNumber)) {
						   $ccType = $ccTypeMatch;
						   break;
					  }
				 }

				 if (!$this->OtherCcType($info->getCcType()) && $ccType!=$info->getCcType()) {
					  $errorMsg = $this->_getHelper()->__('Credit card number mismatch with credit card type.');
				 }
			}else {
				 $errorMsg = $this->_getHelper()->__('Invalid Credit Card Number');

			}

		}else{

		   $errorMsg = $this->_getHelper()->__('Credit card type is not allowed for this payment method.');

		}
		//validate credit card verification number
		if ($errorMsg === false) {
			$verifcationRegEx = $this->getVerificationRegEx();
			$regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
			if (!$info->getCcCid() || !$regExp || !preg_match($regExp ,$info->getCcCid())){
				 $errorMsg = $this->_getHelper()->__('Please enter a valid credit card verification number.');
			}
		}
		//validando data de expiracao
		if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
			$errorMsg = $this->_getHelper()->__('Incorrect credit card expiration date.');
		}		
		return $errorMsg;
	}



	/**
	* Validate credit card number
	*
	* @param   string $cc_number
	* @return  bool
	*/
	public function validateCcNum($ccNumber)	{

	  $cardNumber = strrev($ccNumber);
	  $numSum = 0;

	  for ($i=0; $i<strlen($cardNumber); $i++) {
			$currentNum = substr($cardNumber, $i, 1);

			/**
			 * Double every second digit
			 */
			if ($i % 2 == 1) {
			    $currentNum *= 2;
			}

			/**
			 * Add digits of 2-digit numbers together
			 */
			if ($currentNum > 9) {
			    $firstNum = $currentNum % 10;
			    $secondNum = ($currentNum - $firstNum) / 10;
			    $currentNum = $firstNum + $secondNum;
			}

			$numSum += $currentNum;
	  }
	  /**
		* If the total has no remainder it's OK
		*/
	  return ($numSum % 10 == 0);
	}

	/**
	* Other credit cart type number validation
	*
	* @param string $ccNumber
	* @return boolean
	*/
	public function validateCcNumOther($ccNumber){
	  return preg_match('/^\\d+$/', $ccNumber);
	}

    public function OtherCcType($type)  {
        return $type=='OT';
    }

    public function getVerificationRegEx() {
        $verificationExpList = array(
            'VI' => '/^[0-9]{3}$/', // Visa
            'MC' => '/^[0-9]{3}$/',       // Master Card
            'AE' => '/^[0-9]{4}$/',        // American Express
            'DI' => '/^[0-9]{3}$/',          // Discovery
            'SS' => '/^[0-9]{3,4}$/',
            'SM' => '/^[0-9]{3,4}$/', // Switch or Maestro
            'SO' => '/^[0-9]{3,4}$/', // Solo
            'OT' => '/^[0-9]{3,4}$/',
            'JCB' => '/^[0-9]{4}$/' //JCB
        );
        return $verificationExpList;
    }

	 public function _removeDelimiters($ccNumber){
		// remove credit card number delimiters such as "-" and space
		return preg_replace('/[\-\s]+/', '', $ccNumber);
	 }

    protected function _validateExpDate($expYear, $expMonth)    {
        $date = Mage::app()->getLocale()->date();
        if (!$expYear || !$expMonth || ($date->compareYear($expYear)==1) || ($date->compareYear($expYear) == 0 && ($date->compareMonth($expMonth)==1 )  )) {
            return false;
        }
        return true;
    }

	public function getOrderPlaceRedirectUrl(){
		if(Mage::getStoreConfig('payment/gpl/contrato_tipo') == "BUY_PAGE_CIELO"){
			return Mage::getUrl('gpl/gpl/buypagecielo');
		}elseif(Mage::getStoreConfig('payment/gpl/contrato_tipo') == "BUY_PAGE_LOJA"){
			return Mage::getUrl('gpl/gpl/buypageloja');
		}

	}

 }
 ?>

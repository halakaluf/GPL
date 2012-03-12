 <?php

 /**
 * Our test CC module adapter
 */

 class Conamore_Gpl_Model_Boletos extends Mage_Payment_Model_Method_Abstract {
     /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
     protected $_code = 'boletos';
 
     /**
      * Here are examples of flags that will determine functionality availability
      * of this module to be used by frontend and backend.
      *
      * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
      *
      * It is possible to have a custom dynamic logic by overloading
      * public function can* for each flag respectively
      */
		protected $_formBlockType = 'gpl/form_boletos';
		protected $_infoBlockType = 'gpl/info_boletos';
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
      * Here you will need to implement authorize, capture and void public methods
      *
      * @see examples of transaction specific public methods such as
      * authorize, capture and void in Mage_Paygate_Model_Authorizenet
      */

    /**
     * Assign data to info model instance 
	  *	primeiro  a ser chamado na fila
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)    {

		  return $this;
    }



    /**
     * Prepare info instance for save
     *
	  *	terceiro  a ser chamado na fila
     * @return Mage_Payment_Model_Abstract
     */
    public function prepareSave()    {

        return $this;
    }


    /**
     * Validate payment method information object
	  *	segundo  a ser chamado na fila
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate(){
		/*
		* calling parent validate function
		*/
		parent::validate();
		//This must be after all validation conditions
		if ($this->getIsCentinelValidationEnabled()) {
			$this->getCentinelValidator()->validate($this->getCentinelValidationData());
		}

		return $this;

	 }

	public function getOrderPlaceRedirectUrl(){
		return Mage::getUrl('gpl/itau/dadosboleto');
	}

 }
 ?>

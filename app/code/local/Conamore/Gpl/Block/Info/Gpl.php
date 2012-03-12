<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Authorizenet
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * DirectPost information block
 *
 * @category   Mage
 * @package    Mage_Authorizenet
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Conamore_Gpl_Block_Info_Gpl extends Mage_Core_Block_Template
{    


    protected function _construct()    {
        parent::_construct();
        $this->setTemplate('gpl/info/info.phtml');
    }



    protected function _prepareInfo()    {
		Mage::log("-----------------Info-------prepareInfo----------------------------------------------");
	 }

    //protected function _beforeToHtml()    {
    //    return parent::_beforeToHtml();
    //}


	public function montaInfoBuyPageLoja(){

		$body = "";
		$info = $this->getInfo();
		$nomeCartao = "";
		switch ($info->getCcType()) {
			 case "AE":
				  $nomeCartao = "American express";
				  break;
			 case "MC":
				  $nomeCartao = "Mastercard";
				  break;
			 case "VI":
				  $nomeCartao = "Visa";
				  break;
		}

		$body .= "<p>" .Mage::getStoreConfig('payment/gpl/title') . "</p>";
		$body .= "<p>" .$nomeCartao. "</p>";
		$body .= "<p>xxxx-xxxx-xxxx-" .$info->getCcLast4(). "</p>";
		$body .= "<p>" .$info->getCcOwner(). "</p>";


            
		return $body;
	}

	public function montaInfoBuyPageCielo(){

		$body = "";
		$info = $this->getInfo();
		$nomeCartao = "";
		switch ($info->getCcType()) {
			 case "AE":
				  $nomeCartao = "American express";
				  break;
			 case "MC":
				  $nomeCartao = "Mastercard";
				  break;
			 case "VI":
				  $nomeCartao = "Visa";
				  break;
		}

		$body .= "<p>" .Mage::getStoreConfig('payment/gpl/title') . "</p>";
		$body .= "<p>" .$nomeCartao. "</p>";
            
		return $body;
	}


}

<?php

class Puranatura_Core_Helper_Data extends Mage_Core_Helper_Abstract {
	const REFERRAL_DATA_COOKIE_NAME = 'p_id_';
	
	
	public function setReferralData($productId)
	{
		$cookie = Mage::getSingleton('core/cookie');
		$domain = $this->getCookieDomain($cookie);
		$path = $cookie->getPath();
		$cookie->delete(self::REFERRAL_DATA_COOKIE_NAME . $productId, $path, $domain);
		if($productId)
		{
			$encodeData = Mage::helper('core')->jsonEncode($productId);
			$cookieData = Mage::getSingleton("core/encryption")->encrypt($productId);
			$cookie->set(self::REFERRAL_DATA_COOKIE_NAME . $productId, $cookieData, true, $path, $domain);
		}
	}
	
	public function getCookieDomain($cookie)
	{
		$domain = $cookie->getDomain();
		if (!empty($domain[0]) && ($domain[0] !== '.')) {
			$domain = '.'.$domain;
		}
		return $domain;
	}
	
	public function getReferralData($productId)
	{
		$data = Mage::getSingleton("core/encryption")->decrypt(Mage::getSingleton('core/cookie')->get(self::REFERRAL_DATA_COOKIE_NAME . $productId));
		if($data)
		{
			return Mage::helper('core')->jsonDecode($data);
		}
		return false;
	}
	
	public function getProductUrl($_product)
	{
		$productUrl = $_product->getProductUrl();
		
		return $productUrl;
	}
	
}
?>
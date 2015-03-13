<?php
/**
 * 
 * @author ddikovic
 *
 */

class Westum_Followupemail_Helper_Data extends Mage_Core_Helper_Abstract
{
    /** Magento 1.3.* version code */
    const MAGENTO_VERSION_CE_1_3 = 'CE13';
    /** Magento 1.4.* version code */
    const MAGENTO_VERSION_CE_1_4 = 'CE14';
    # Magento 1.8.* version code
    const MAGENTO_VERSION_EE_18 = 'EE18';
    # Magento 1.9.* version code
    const MAGENTO_VERSION_EE_19 = 'EE19';
    # Starting version of AN to integration
    const FUE_AN_MINVERSION = '2.0.3';

    private $_canUseAN = null;

    /***
     * Returns Magento version code
     *
     * @return string
     */
    public static function getMagentoVersionCode()
    {
        if (preg_match('|1\.4.*|', Mage::getVersion())) {
            return self::MAGENTO_VERSION_CE_1_4;
        }
        if (preg_match('|1\.8.*|', Mage::getVersion())) {
            return self::MAGENTO_VERSION_EE_18;
        }
        if (preg_match('|1\.9.*|', Mage::getVersion())) {
            return self::MAGENTO_VERSION_EE_19;
        }

        return self::MAGENTO_VERSION_CE_1_3;
    }

    public function checkVersion($version)
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    /**
     * Removes empty values from the array given
     * @param mixed $data Array to inspect or data to be placed in new array as first value
     * @return array Array processed
     */
    public static function noEmptyValues($data)
    {
        $result = array();
        if (is_array($data)) {
            foreach ($data as $a) {
                if ($a) {
                    $result[] = $a;
                }
            }
        } else {
            $result = $data ? array() : array($data);
        }
        return $result;
    }

    /**
     * Explodes "Copy to" email list
     * @param string Email addresses separated by commas, spaces, or semicolons
     * @return array Array containing single email in each value
     */
    public static function explodeEmailList($emails)
    {
        if (!$emails) {
            return array();
        }
        $emails = trim(str_replace(array(',', ';'), ' ', $emails));
        do {
            $emails = str_replace('  ', ' ', $emails);
        } while (strpos($emails, '  ') !== false);
        $result = explode(' ', $emails);
        return $result;
    }

    /**
     * Returns multi-level array dump by calling itself
     * @param array $source
     * @return string
     */
    public static function printFlatArray($source)
    {
        $isFirst = true;
        $res = '*array(';
        foreach ($source as $k => $v) {
            $res .= ($isFirst ? ($isFirst = false) : ',') . (is_array($v) ? ($k . '=' . self::printFlatArray($v)) : $v);
        }
        return $res . ')*';
    }

    /**
     * Returns array dump
     * @param array @params
     * @return string params
     */
    public static function printParams($params)
    {
        $res = '{';
        foreach ($params as $k => $v) {
            $res .= $k . '=' . ((is_scalar($v) || is_null($v))
                    ? $v : ((is_array($v) ? self::printFlatArray($v) : '*object*'))) . '; ';
        }
        return $res . '}';
    }

    /**
     * Returns unique security code
     * @return string
     */
    public static function getSecurityCode()
    {
        return md5(mt_rand());
    }

    /**
     * Returns humanic-like text telling how many days, hours, and minutes left/passed before or after some moment
     * @param int $days Days number
     * @param int $hours Hours number
     * @param int $minutes Minutes number
     * @param int $before Is it before or after
     * @return string
     */
    public function getTimeDelayText($days, $hours, $minutes, $before = 1)
    {
        if ($days) {
            $result = $days . ' ' . $this->__('day(s)');
        } elseif ($hours) {
            $result = $hours . ' ' . $this->__('hour(s)');
        } elseif ($minutes) {
            $result = $minutes . ' ' . $this->__('minutes(s)');
        } else {
            $result = $this->__('right after');
        }

        if ($days || $hours || $minutes) {
            $result .= ' ' . (($before == 1) ? $this->__('before') : $this->__('after'));
        }
        return $result;
    }

    /**
     * If Westum_Customsmtp installed and not disabled and if value of option 'Send
     * emails' is equal to 'Send From Custom SMTP Server' returns array with
     * SMTP settings or FALSE otherwise
     */
    public static function getCustomSMTPSettings()
    {
        $_modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (array_key_exists('Westum_Customsmtp', $_modules) && 'true' == (string)$_modules['Westum_Customsmtp']->active
            && !(bool)Mage::getStoreConfig('advanced/modules_disable_output/Westum_Customsmtp')
            && Mage::getStoreConfig(Westum_Customsmtp_Helper_Config::XML_PATH_ENABLED)
            && Mage::getStoreConfig('customsmtp/general/mode') == Westum_Customsmtp_Model_Source_Mode::ON
        ) {
            $customSMTPSettings = array(
                'port' => Mage::getStoreConfig(Westum_Customsmtp_Helper_Config::XML_PATH_SMTP_PORT),
                'auth' => Mage::getStoreConfig(Westum_Customsmtp_Helper_Config::XML_PATH_SMTP_AUTH),
                'username' => Mage::getStoreConfig(Westum_Customsmtp_Helper_Config::XML_PATH_SMTP_LOGIN),
                'password' => Mage::getStoreConfig(Westum_Customsmtp_Helper_Config::XML_PATH_SMTP_PASSWORD)
            );

            $needSSL = Mage::getStoreConfig(Westum_Customsmtp_Helper_Config::XML_PATH_SMTP_SSL);
            if (!empty($needSSL)) {
                $customSMTPSettings['ssl'] = strtolower(
                    Mage::getStoreConfig(Westum_Customsmtp_Helper_Config::XML_PATH_SMTP_SSL)
                );
            }

            return $customSMTPSettings;
        }
        return false;
    }

    /*
     * Checks whether Market Segmentation Suite extension by aheadWorks is installed and not disabled
     * @return bool
     */
    public static function isMSSInstalled()
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        return array_key_exists('Westum_Marketsuite', $modules)
            && 'true' == (string)$modules['Westum_Marketsuite']->active
            && !Mage::getStoreConfig('advanced/modules_disable_output/Westum_Marketsuite')
        ;
    }

    /*
     * Checks whether Who Bought This Also Bought extension by aheadWorks is installed and not disabled
     * @return bool
     */
    public static function isWBTABInstalled()
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        return array_key_exists('Westum_Relatedproducts', $modules)
            && 'true' == (string)$modules['Westum_Relatedproducts']->active
            && !Mage::getStoreConfig('advanced/modules_disable_output/Westum_Relatedproducts')
        ;
    }

    /*
    * Checks whether Automatic Related Products 2 extension by aheadWorks is installed and not disabled
    * @return bool
    */
    public static function isARP2Installed()
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        return array_key_exists('Westum_Autorelated', $modules)
            && 'true' == (string)$modules['Westum_Autorelated']->active
            && !Mage::getStoreConfig('advanced/modules_disable_output/Westum_Autorelated')
        ;
    }

    /**
     * Returns TRUE if AN is installed and has version >= self::FUE_AN_MINVERSION
     * that is required for integration using API
     *
     * @return bool
     */
    public function canUseAN()
    {
        if ($this->_canUseAN === null) {
            $modules = (array)Mage::getConfig()->getNode('modules')->children();
            $_isInstalled = array_key_exists('Westum_Advancednewsletter', $modules)
                && 'true' == (string)$modules['Westum_Advancednewsletter']->active
                && !(bool)Mage::getStoreConfig('advanced/modules_disable_output/Westum_Advancednewsletter');
            $this->_canUseAN = $_isInstalled
                && version_compare(
                    $modules['Westum_Advancednewsletter']->version, self::FUE_AN_MINVERSION, '>='
                );
        }
        return $this->_canUseAN;
    }

    public function getMinANVersion()
    {
        return self::FUE_AN_MINVERSION;
    }

    /**
     * Returns order address by type
     * @param Mage_Sales_Model_Order $order Order to inspect
     * @param string $addressType Address type (billing or shipping)
     * @return Mage_Sales_Model_Entity_Order_Address|false Address (if found)
     */
    public static function getOrderAddress($order, $addressType)
    {
        $addresses = Mage::getResourceModel('sales/order_address_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('parent_id', $order->getId())
        ;

        if ($order->getId()) {
            foreach ($addresses as $address) {
                $address->setOrder($order);
            }
        }

        foreach ($addresses as $address) {
            if ($addressType == $address->getAddressType() && !$address->isDeleted()) {
                return $address;
            }
        }

        return false;
    }

    /*
     * Returns current Link Tracking query type
     * @return string
     */
    public static function getLinktrackingQueryType()
    {
        $queryType = Mage::app()->getRequest()->getParam('queryType');
        if (!$queryType) {
            $queryType = Westum_Followupemail_Model_Source_Linktracking_Types::LINKTRACKING_TYPE_LINK_ONLY;
        }
        return $queryType;
    }

    /**
     * Returns is TBT rewards installed
     */
    public static function isTBTRewardsInstalled()
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        return array_key_exists('TBT_Rewards', $modules)
            && 'true' == (string)$modules['TBT_Rewards']->active
            && !(bool)Mage::getStoreConfig('advanced/modules_disable_output/TBT_Rewards')
            && @class_exists('TBT_Rewards_Model_Newsletter')
        ;
    }

    function convertPrice($price, $currencyFrom, $currencyTo)
    {
        $currencyResourceModel = Mage::getResourceModel('directory/currency');
        $_rate = $currencyResourceModel->getRate($currencyFrom, $currencyTo);
        if (!$_rate) {
            $_rate = 1 / $currencyResourceModel->getRate($currencyTo, $currencyFrom);
        }
        if (!$_rate) {
            return false;
        }
        return $price * $_rate;
    }

    /**
     * @param $rule
     *
     * @return string
     * Return google tracking code parameters
     */
    public function getGaConfig($rule)
    {
        if (!method_exists(Mage::helper('googleanalytics'), 'isGoogleAnalyticsAvailable')
            || !Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()
            || !$rule->getData('ga_source')
            || !$rule->getData('ga_medium')
            || !$rule->getData('ga_name')
        ) {
            return;
        }
        $gaParams = '?utm_source=' . rawurlencode($rule->getData('ga_source'));
        $gaParams .= '&utm_medium=' . rawurlencode($rule->getData('ga_medium'));
        $gaParams .= '&utm_campaign=' . rawurlencode($rule->getData('ga_name'));
        if ($rule->getData('ga_term') != '') {
            $gaParams .= '&utm_term=' . rawurlencode($rule->getData('ga_term'));
        }
        if ($rule->getData('ga_content') != '') {
            $gaParams .= '&utm_content='
                . rawurlencode($rule->getData('ga_content'))
            ;
        }
        return $gaParams;
    }

    public function isExtensionInstalled($name)
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        return array_key_exists($name, $modules)
            && 'true' == (string)$modules[$name]->active
            && !(bool)Mage::getStoreConfig('advanced/modules_disable_output/' . $name)
        ;
    }

    public function checkExtensionVersion($extensionName, $extVersion, $operator = '>=')
    {
        if ($this->isExtensionInstalled($extensionName)
            && ($version = Mage::getConfig()->getModuleConfig($extensionName)->version)
        ) {
            return version_compare($version, $extVersion, $operator);
        }
        return false;
    }

    /**
     * Return logo URL for emails
     * Take logo from skin if custom logo is undefined
     *
     * @param  Mage_Core_Model_Store|int|string $store
     * @return string
     *
     * @see Mage_Core_Model_Email_Template::_getLogoUrl()
     */
    public function getLogoUrl($store)
    {
        $store = Mage::app()->getStore($store);

        $currentDesign = Mage::getDesign()->setAllGetOld(
            array(
                 'package' => Mage::getStoreConfig('design/package/name', $store->getId()),
                 'area'    => Mage_Core_Model_Design_Package::DEFAULT_AREA,
                 'store'   => Mage::app()->getStore( $store->getId()),
            )
        );

        if (Mage::helper('followupemail')->checkVersion('1.7')) {
            $fileName = $store->getConfig(Mage_Core_Model_Email_Template::XML_PATH_DESIGN_EMAIL_LOGO);
            if ($fileName) {
                $uploadDir = Mage_Adminhtml_Model_System_Config_Backend_Email_Logo::UPLOAD_DIR;
                $fullFileName = Mage::getBaseDir('media') . DS . $uploadDir . DS . $fileName;
                if (file_exists($fullFileName)) {
                    return Mage::getBaseUrl('media') . $uploadDir . '/' . $fileName;
                }
            }
        }
        $logoUrl = Mage::getDesign()->getSkinUrl('images/logo_email.gif');
        Mage::getDesign()->setAllGetOld($currentDesign);
        return $logoUrl;
    }

    /**
     * Return logo alt for emails
     *
     * @param  Mage_Core_Model_Store|int|string $store
     * @return string
     *
     * @see Mage_Core_Model_Email_Template::_getLogoAlt()
     * @
     */
    public function getLogoAlt($store)
    {
        $store = Mage::app()->getStore($store);
        if (Mage::helper('followupemail')->checkVersion('1.7')) {
            $alt = $store->getConfig(Mage_Core_Model_Email_Template::XML_PATH_DESIGN_EMAIL_LOGO_ALT);
            if ($alt) {
                return $alt;
            }
        }
        return $store->getFrontendName();
    }
}
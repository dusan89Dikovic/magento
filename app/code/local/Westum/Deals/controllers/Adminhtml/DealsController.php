<?php

class Westum_Deals_Adminhtml_DealsController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('westum_deals/adminhtml_deals', 'westum_deals_block'));
        $this->renderLayout();
    }

    public function newAction() {

        $this->_forward('edit');
    }

    public function editAction() {

        /* register in global registry */
        Mage::register('westum_deal_registry', $this->_initDeal(), true);

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    public function saveAction() {

        $req = $this->getRequest();
        try {
            $data = $req->getPost();
            /* Convert dates to MYSQL fromat */
            $this->_prepareForSave($data);
            $this->_convertDateParts($data);

            $model = Mage::getModel('westum_deals/deal');
            if ($id = $req->getParam('id')) {
                $model->load($id);
            }
            /*       
            if($deals = $model->alreadyActive($data)) {    
               throw new Exception(Mage::helper('westum_deals')->__('There is already active deals with ids ( %s ) on the same store view related to the same product', $deals));
            } */  
          
            Westum_Deals_Model_Mysql4_Deal::$_updateRelated = true;
            $model->addData($data)->save();
            Mage::getSingleton('adminhtml/session')->addSuccess('Deal has been successfully saved');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage())->setElementData($data);
            return $this->_redirect('*/*/edit', array('id' => $req->getParam('id')));
        }
      
        if($req->getParam('back') == 'edit') {            
            return $this->_redirect('*/*/edit', array('id' => $model->getId(),'tab' => $this->getRequest()->getParam('tab')));
        }

        $this->_redirect('*/*');
    }

    /* Product grid action */

    public function gridAction() {

        $this->getResponse()->setBody($this->getLayout()->createBlock('westum_deals/adminhtml_deals_products_grid')->toHtml());
    }

    public function ordersGridAction() {

        $this->getResponse()->setBody($this->getLayout()->createBlock('westum_deals/adminhtml_deals_orders_grid')->toHtml());
    }

    public function categoriesAction() {

        /* register in global registry */
        Mage::register('current_product', $this->_prepareCategories($this->_initDeal()), true);

        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_categories')->toHtml()
        );
    }

    public function categoriesJsonAction() {

        Mage::register('current_product', $this->_prepareCategories($this->_initDeal()), true);

        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_categories')
                        ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
    
    public function exportCsvAction() {
        $fileName = 'deals.csv';
        $content  = $this->getLayout()->createBlock('westum_deals/adminhtml_deals_grid')->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName   = 'deals.xml';
        $content    = $this->getLayout()->createBlock('westum_deals/adminhtml_deals_grid')->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _initDeal() {

        $id = (int) $this->getRequest()->getParam('id');

        $dealModel = Mage::getModel('westum_deals/deal')->load($id);

        return $dealModel;
    }

    protected function _prepareCategories(Varien_Object $obj) {

        $obj->setCategoryIds(array_unique(explode(',', $obj->getCategories())));

        return $obj;
    }

    protected function _convertDateParts(&$data) {

        $locale = Mage::app()->getLocale();
        $format = Mage::app()->getLocale()->getTranslation(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, 'datetime');

        if (!empty($data['available_to'])) {
            $dateTo = $locale->date($data['available_to'], $format);
            $data['available_to'] = gmdate(Westum_Deals_Helper_Data::MYSQL_DATE_TIME_FROMAT, $dateTo->get(Zend_Date::TIMESTAMP) - $dateTo->get(Zend_Date::TIMEZONE_SECS));
        } else {
            $data['available_to'] = null;
        }
        if (!empty($data['available_from'])) {
            $dateFrom = $locale->date($data['available_from'], $format);
            $data['available_from'] = gmdate(Westum_Deals_Helper_Data::MYSQL_DATE_TIME_FROMAT, $dateFrom->get(Zend_Date::TIMESTAMP) - $dateFrom->get(Zend_Date::TIMEZONE_SECS));
        } else {
            $data['available_from'] = null;
        }
        return $data;
    }

    protected function _prepareForSave(&$data) {

        foreach ($data as $key => $info) {

            if (preg_match('#^' . Westum_Deals_Helper_Data::APP_PREFIX . '_(.+)$#is', $key, $match)) {

                $data[$match[1]] = $info;
            }
            
            if ($key == 'additional_settings_img_resize') {
                $data['additional_settings'][$key] = (int) trim($info) === 0 ? Westum_Deals_Model_Source_Design::IMAGE_RESIZE : (int) trim($info);
            }
            else if($key == 'additional_settings_img_resize_height') {                            
              $data['additional_settings'][$key] = (int) trim($info) === 0 ? Westum_Deals_Model_Source_Design::IMAGE_RESIZE_H : (int) trim($info);           
            }
            else if($key == 'additional_settings_cms_resize') {   
                $data['additional_settings'][$key] = (int) trim($info) === 0 ? Westum_Deals_Model_Source_Design::IMAGE_CMS : (int) trim($info);                
            }
            else if($key == 'additional_settings_cms_resize_height') {                
                $data['additional_settings'][$key] = (int) trim($info) === 0 ? Westum_Deals_Model_Source_Design::IMAGE_CMS_RESIZE_H : (int) trim($info);    
            }
            
            if($key == 'additional_settings_product_price') {
                $data['additional_settings'][$key] = (int) trim($info);                
            }
            
            if($key == 'additional_settings_rewrite_styles') {                
                $data['additional_settings'][$key] = trim($info);    
            }
           
        }
        $data['additional_settings'] = @serialize($data['additional_settings']);
        
        if(!isset($data['customer_group_ids'])) {
            $data['customer_group_ids'] = false;
        }
       
        return $data;
    }

    public function deleteAction() {

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('westum_deals/deal');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The deal has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find a deal to delete.'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {

        $deals = $this->getRequest()->getParam('westum_deals');
        if (!is_array($deals)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select any elements'));
        } else {
            if (!empty($deals)) {
                try {
                    foreach ($deals as $deal) {
                        Mage::getSingleton('westum_deals/deal')->setId($deal)->delete();
                    }
                    $this->_getSession()->addSuccess(
                            $this->__('Total of %d record(s) have been deleted.', count($deals))
                    );
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {

        $deals = $this->getRequest()->getParam('westum_deals');
        $status = (int) (bool) $this->getRequest()->getParam('status');

        if (!is_array($deals)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select any elements'));
        } else {
            if (!empty($deals)) {
                try {
                    foreach ($deals as $deal) {
                        $dayDeal = Mage::getModel('westum_deals/deal')->load($deal);
                      
                      /*
                      if($dayDeal->getProductId() && $status) {
                        if($duplicates = $dayDeal->alreadyActive()) {                            
                            $this->_getSession()->addError(
                                Mage::helper('westum_deals')->__('Deal %d cannot be enabled as there are already active deals with ids:( %s ) on the same store view 
                                    related to product %d. Please disable them to enable deal #%d', $dayDeal->getId(), $duplicates, $dayDeal->getProductId(), $dayDeal->getId())
                            );                            
                            continue;
                        }
                      }*/
                        
                        if ($dayDeal->getId()) {
                            $dayDeal->setStatus($status)->save();
                            $this->_getSession()->addSuccess(
                                $this->__('Total of %d record(s) have been successfully modified', count($deals))
                            );
                        }
                    }
                    
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massPriceAction() {

        $deals = $this->getRequest()->getParam('westum_deals');
        $price = (float) $this->getRequest()->getParam('price');

        if (!is_array($deals)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select any elements'));
        } else {
            if (!empty($deals)) {
                try {
                    foreach ($deals as $deal) {
                        $dayDeal = Mage::getModel('westum_deals/deal')->load($deal);
                        if ($dayDeal->getId()) {
                            $dayDeal->setPrice($price)->save();
                        }
                    }
                    $this->_getSession()->addSuccess(
                            $this->__('Total of %d record(s) have been successfully modified', count($deals))
                    );
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massPositionAction() {

        $deals = $this->getRequest()->getParam('westum_deals');
        $positions = $this->getRequest()->getParam('magalter_positions');

        if (!is_array($deals)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select any elements'));
        } else {
            if (!empty($deals)) {
                try {
                    foreach ($deals as $deal) {
                        $dayDeal = Mage::getModel('westum_deals/deal')->load($deal);
                        if ($dayDeal->getId()) {
                            // see _beforeSave model
                            $dayDeal->setPosition($positions)->save();
                        }
                    }
                    $this->_getSession()->addSuccess(
                            $this->__('Total of %d record(s) have been successfully modified', count($deals))
                    );
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

}
<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isDebug()
    {
        $storeId = Mage::app()->getStore()->getId();
        $debugMode = Mage::getStoreConfig('extensions_store_storealerts/configuration/debug_mode', $storeId);
        
        return ($debugMode) ? true : false;
    }
    
    public function log($message, $level=null)
    {
        if ($this->isDebug() || (int)$level < 4){
            
            Mage::log($message, $level, 'extensions_store_storealerts.log');
        }
    }
    
    /**
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function login($username, $password) {

    	$admin = Mage::getModel('admin/user');
    	$admin->login($username, $password);
        
    	return $admin;
    }    
    
    /**
     * 
     * @return string
     */
    public function getApiUrl()
    {
    	return 'https://api.extensions-store.com';
    }

    /**
     * 
     * @return string
     */
    public function getApiHost()
    {
    	return 'api.extensions-store.com';
    }
    
    public function getDomain()
    {
    	$domain = '';
    	
    	if (isset($_SERVER['HTTP_HOST'])){
    		
    		$domain = $_SERVER['HTTP_HOST'];
    		
    	} else {
    		
    		$baseUrl = Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB);   
    		$urlPartsAr = parse_url($baseUrl);
    		
    		if (isset($urlPartsAr['host'])){
    			$domain = $urlPartsAr['host'];
    		}
    	}
    	
    	return $domain;
    }
    
    /**
     * Register alert for each subscriber
     *
     * @param string $type
     * @param string $message
     * @param string $title
     * @param string $datetime
     */
    public function saveAlert($type, $message, $title = null, $datetime = null)
    {
    	if ($type == ExtensionsStore_StoreAlerts_Model_Alert::LOG || $type == ExtensionsStore_StoreAlerts_Model_Alert::EXCEPTION){
    		$alerts = Mage::getModel('storealerts/alert')->getCollection();
    		$length = Mage::getStoreConfig('extensions_store_storealerts/configuration/duplicate_log_length');
    		$alerts->addFieldToFilter(new Zend_Db_Expr("LEFT(message,$length)"), substr($message,0,$length));
    		$today = date('Y-m-d');
    		$alerts->addFieldToFilter('created_at', array('gteq' => $today));
    		$limit = Mage::getStoreConfig('extensions_store_storealerts/configuration/duplicate_log_limit');
    		$size = $alerts->getSize();
    		$sql = (string)$alerts->getSelect();
    		if ($size >= $limit){
    			return;
    		}    		
    	}

    	try {
    
    		$preferences = Mage::getModel('storealerts/preference')->getCollection();
    		$types = Mage::getModel('storealerts/alert')->getTypes();
    		$label = $types[$type]['label'];
    		$title = ($title) ? $title : $types[$type]['title'];
    		$userIds = $preferences->getAllIds();
    		$admins = Mage::getModel('admin/user')->getCollection();
    		$admins->addFieldToFilter('is_active', 1);
    		$admins->addFieldToFilter('user_id', array('in' => $userIds));
    		$adminIds = $admins->getAllIds();
    		$datetime = ($datetime) ? $datetime : date('Y-m-d H:i:s');
    		//log for admin id 0
    		if ($type == ExtensionsStore_StoreAlerts_Model_Alert::LOG || $type == ExtensionsStore_StoreAlerts_Model_Alert::EXCEPTION){
	    		$this->_saveAlert($type, $label, $title, $message, 'default', 0, $datetime);
    		}
    		
    		foreach ($preferences as $preference){
    			
    			$userId = $preference->getId();
    			     			
    			if (in_array($userId, $adminIds)){
    				
    				$alertsStr = trim($preference->getAlerts());
    				$selectedAlerts = explode(',', $alertsStr);
    				
    				if (is_array($selectedAlerts) && in_array($type, $selectedAlerts)){
    				
    					$alertIndex = array_search($type, $selectedAlerts);
    				
    					$soundsStr = trim($preference->getSounds());
    					$alertSounds = explode(',', $soundsStr);
    					$sound = (is_array($alertSounds) && count($alertSounds) == count($selectedAlerts)) ?
    					$alertSounds[$alertIndex] : 'default';
    				
    					$this->_saveAlert($type, $label, $title, $message, $sound, $userId, $datetime);
    				
    				}
    				
    			}
    
    		}
    
    
    	} catch(Exception $e){
    
    		$this->log($e->getMessage(), 3);
    	}
    
    }    
    
    /**
     * 
     * @param string $type
     * @param string $label
     * @param string $title
     * @param string $message
     * @param string $sound
     * @param string $userId
     * @param string $datetime
     */
    protected function _saveAlert($type, $label, $title, $message, $sound, $userId, $datetime){
    	
    	$alert = Mage::getModel('storealerts/alert');
    	$alert->setType($type);
    	$alert->setLabel($label);
    	$alert->setTitle($title);
    	$alert->setMessage($message);
    	$alert->setSound($sound);
    	$alert->setUserId($userId);
    	$alert->setSent(0);
    	$alert->setCreatedAt($datetime);
    	$alert->setUpdatedAt($datetime);
    	$alert->save();
    }
    
}
<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_logLevel;
    
    /**
     * 
     * @return bool
     */
    public function isDebug()
    {
        $storeId = Mage::app()->getStore()->getId();
        $debugMode = Mage::getStoreConfig('extensions_store_storealerts/configuration/debug_mode', $storeId);
        
        return ($debugMode) ? true : false;
    }
    
    /**
     *  @param string $message
     *  @@param int $level
     */
    public function log($message, $level=null)
    {
        if ($this->isDebug() || (int)$level < 4){
            
            Mage::log($message, $level, 'extensions_store_storealerts.log');
        }
    }
    
    /**
     * @return bool
     */
    public function logPriority($priority){
        if (!$this->_logLevel){
            $this->_logLevel = (int)Mage::getStoreConfig('extensions_store_storealerts/configuration/log_level');
        }        
        
        if (is_numeric($priority) && $priority <= $this->_logLevel){
            return true;
        }
        
        return false;
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
    
    /**
     * Get host domain name of your site
     * @return string
     */
    public function getDomain()
    {
    	$domain = '';
    	
    	if (isset($_SERVER['HTTP_HOST'])){
    		
    		$domain = $_SERVER['HTTP_HOST'];
    		$hostname = Mage::getStoreConfig('extensions_store_storealerts/configuration/hostname');
    		
    		if ($hostname && $hostname != $domain){
    		    $domain = $hostname;
    		}
    		
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
     * @return bool 
     */
    public function saveAlert($type, $message, $title = null, $datetime = null)
    {
        $message = trim($message);
        
    	if ($type == ExtensionsStore_StoreAlerts_Model_Alert::LOG || $type == ExtensionsStore_StoreAlerts_Model_Alert::EXCEPTION){
    		$alerts = Mage::getModel('storealerts/alert')->getCollection();
    		$length = Mage::getStoreConfig('extensions_store_storealerts/configuration/duplicate_log_length');
    		//check if same message at same time
    		$alerts->addFieldToFilter(new Zend_Db_Expr("LEFT(message,$length)"), substr($message,0,$length));
    		$createdAt = ($datetime) ? date('Y-m-d H:i:s', strtotime($datetime)) :  date('Y-m-d H:i:s');
    		$alerts->addFieldToFilter('created_at', array('eq' => $createdAt));
    		$size = $alerts->getSize();
    		$sql = (string)$alerts->getSelect();
    		if ($size > 0){
    		    return false;
    		}
    		//check if message was already logged in the last 24 hours
    		$alerts = Mage::getModel('storealerts/alert')->getCollection();
    		$alerts->addFieldToFilter(new Zend_Db_Expr("LEFT(message,$length)"), substr($message,0,$length));
		    $timeStart = ($datetime) ? strtotime($datetime)  : time();
		    $timeEnd = ($datetime) ? strtotime($datetime) + 86400 : time() + 86400;
		    $dateStart = date('Y-m-d 00:00:01', $timeStart);
		    $dateEnd = date('Y-m-d H:i:s', $timeEnd);
		    $alerts->addFieldToFilter('created_at', array('gteq' => $dateStart));
		    $alerts->addFieldToFilter('created_at', array('lt' => $dateEnd));
    		$sql = (string)$alerts->getSelect();
		    $size = $alerts->getSize();
    		$limit = Mage::getStoreConfig('extensions_store_storealerts/configuration/duplicate_log_limit');
    		if ($size >= $limit){
    			return false;
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
    	
    	return true;
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
<?php

/**
 * 
 * @category    ExtensionsStore
 * @package     ExtensionsStore_StoreAlerts
 * @author      Extensions Store <admin@extensions-store.com>
 */

class ExtensionsStore_StoreAlerts_Model_Cron
{
    /**
     * 
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function pushAlerts($schedule)
    {
		$numPushed = 0;
    	$push = Mage::getSingleton('storealerts/push');
    	
    	$helper = Mage::helper('storealerts');
    	$alerts = Mage::getModel('storealerts/alert')->getCollection();
    	$alerts->addFieldToFilter('sent',0);
    	$updatedAt = date('Y-m-d H:i:s', strtotime('-1 hour')); //only push alerts generated in last hour
    	$alerts->addFieldToFilter('updated_at',array('gteq' => $updatedAt));
    	
    	if ($alerts->getSize()>0){
    		    		
    		$admins = array();
    		$preferences = array();
    		
    		foreach ($alerts as $alert){
    		
    			$userId = $alert->getUserId();
    			if (!isset($admins[$userId])){
    				$adminUser = Mage::getModel('admin/user')->load($userId);
    				$admins[$userId] = $adminUser;
    			} else {
    				$adminUser = $admins[$userId];
    			}
    			
    			if ($adminUser && $adminUser->getId() && $adminUser->getIsActive()){

    				$type = $alert->getType();
    				$label = $alert->getLabel();
    				$title = $alert->getTitle();
    				$message = $alert->getMessage();
    				$datetime = date('F j, Y g:i a',Mage::getModel('core/date')->timestamp($alert->getUpdatedAt()));
    				
    				//load preferences for this admin
    				if (!isset($preferences[$userId])){
    					$preference = Mage::getModel('storealerts/preference')->load($userId);
    					if ($preference->getSlackHooks()){
    						$slackHooks = explode(PHP_EOL,$preference->getSlackHooks());
    						$slackHooks = array_filter($slackHooks);
    						if (is_array($slackHooks) && count($slackHooks)>0 && $slackHooks[0]){
    							$slackHooksUrls = array();
    							$selectedAlerts = explode(',',$preference->getAlerts());
    							foreach($selectedAlerts as $i=>$selectedAlert){
    								$index = (count($slackHooks) == 1) ? 0 : $i;
    								$slackHooksUrls[$selectedAlert] = $slackHooks[$index];
    							}
    							$preference->setSlackHookUrls($slackHooksUrls);
    						}
    					}
    					$preferences[$userId] = $preference;
    						
    				}else {
    					$preference = $preferences[$userId] ;
    				}
    				
    				//send email alert
    				if ($preference->getEmailAlerts()){
    					$result = $push->pushEmailAlert($adminUser, $label, $datetime, $title, $message);
    					if (!$result){
    					    $logTitle = "Store Alerts could not send alert to email {$adminUser->getEmail()}";
    					    $logMessage = "Alert ID: {$alert->getId()}; Alert title: $title";
    					    $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $logMessage, $logTitle, null);
    					}    					
    				}
    				
    				//send slack hooks
    				if ($preference->getSlackHookUrls()){
    					
    					$slackHookUrls = $preference->getSlackHookUrls();
    					$slackHookUrl = $slackHookUrls[$type];
    					$result = $push->pushSlackHook(trim($slackHookUrl), $message);
    					if (!$result){
    					    $logTitle = "Store Alerts could not push to slack hook: {$slackHookUrl}";
    					    $logMessage = "Alert ID: {$alert->getId()}; Alert title: $title";
    					    $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $logMessage, $logTitle, null);
    					}
    				}
    				
    				//send device alert
    				$sound = $alert->getSound();
    				$devices = Mage::getModel('storealerts/device')->getCollection();
    				$devices->addFieldToFilter('user_id',$userId);
    				
    				foreach ($devices as $device){
    					$deviceToken = $device->getDeviceToken();
    					$accessToken = $device->getAccessToken();
    					if ($deviceToken && $accessToken){
    						$result = $push->push($deviceToken, $accessToken, $adminUser->getEmail(), substr($message, 0, 1900), $sound);
    						if (!$result){
    						    $logTitle = "Store Alerts could not push to device: {$deviceToken}";
    						    $logMessage = "Alert ID: {$alert->getId()}; Alert title: $title";
    						    $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $logMessage, $logTitle, null);
    						}
    					}
    				}
    				
    				$numPushed++;
    				$datetime = date("Y-m-d H:i:s");
    				$alert->setSent(1)->setUpdatedAt($datetime)->save();
    			}
    		}   
    		
    	}
    	
        return 'Number of alerts pushed: '.$numPushed;
        
    }    
    
    /**
     * 
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function checkNotifications($schedule)
    {
    	$numNotifications = 0;
    	$severity = Mage::getStoreConfig('extensions_store_storealerts/configuration/notification_severity');
    	$markNotificationRead = Mage::getStoreConfig('extensions_store_storealerts/configuration/mark_notification_read');
    	 
    	$notifications = Mage::getModel('adminnotification/inbox')->getCollection();
    	$notifications->addFieldToFilter('is_read',0);
    	$notifications->addFieldToFilter('is_remove',0);
    	$notifications->addFieldToFilter('severity',array('lteq' => $severity));
    	$today = date("Y-m-d");
    	$notifications->addFieldToFilter('date_added',array('gteq' => $today));
    	    	
    	if ($notifications->getSize()>0){
    		
    		$helper = Mage::helper('storealerts');
    		
    		foreach ($notifications as $notification){
    				
    			$title = $notification->getTitle();
    		    $message = $notification->getDescription();
    		    $datetime = $notification->getDateAdded();
    				
    			$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::NOTIFICATION, $message, $title, $datetime);
    			
    			if ($markNotificationRead){
    				$notification->setIsRead(1)->save();
    			}
    			
    			$numNotifications++;
    		} 		
    		
    	}
    	     	 
    	return 'Number of notifications submitted: '.$numNotifications;    	
    }
    
    /**
     * Archive logs and reports
     * 
     * @param Mage_Cron_Model_Schedule $schedule
     * @return string
     */
    public function archive($schedule){
        
        $helper = Mage::helper('storealerts');
        $reportsArchived = $this->_archiveExceptionReports();
        $systemArchivePath = Mage::getBaseDir('var').DS.'log'.DS.'system_archive.log';
        $exceptionArchivePath = Mage::getBaseDir('var').DS.'log'.DS.'exception_archive.log';
        
        if (file_exists($systemArchivePath) || file_exists($exceptionArchivePath)){
            $preferences = Mage::getModel('storealerts/preference')->getCollection();
            $preferences->addFieldToFilter('alerts', array('like' => '%log%'));
            if ($preferences->getSize()>0){
                $logs = array();
                if (file_exists($systemArchivePath)){
                    $systemLine = Mage::getBaseDir('var').DS.'log'.DS.'system_line.log';
                    $currentLine = (file_exists($systemLine)) ? file_get_contents($systemLine) : 0;
                    $logs['system'] = array('file'=>$systemArchivePath, 'line_file' => $systemLine, 'current_line'=>$currentLine);;
                }
                if (file_exists($exceptionArchivePath)){
                    $exceptionLine = Mage::getBaseDir('var').DS.'log'.DS.'exception_line.log';
                    $currentLine = (file_exists($exceptionLine)) ? file_get_contents($exceptionLine) : 0;
                    $logs['exception'] = array('file'=>$exceptionArchivePath, 'line_file' => $exceptionLine , 'current_line'=>$currentLine);
                }
                if (count($logs)>0){
                    $limit = 100;
                    foreach ($logs as $type=>$log){
                        $lines = array();
                        $entriesProcessed = 0;
                        $file = $log['file'];
                        $currentLine = (int)trim($log['current_line']);
                        $lineFile = $log['line_file'];
                        $logFile = new SplFileObject($file);
                        $logFile->seek(PHP_INT_MAX);
                        $linesTotal = $logFile->key();
                        $logFile->seek($currentLine);
                        while(!$logFile->eof()){
                            if ($entriesProcessed >= $limit){
                                break;
                            }
                            $logFile->seek($currentLine);
                            $line = $logFile->current();
                            $lines[] = $line;
                            $lastLine = ($currentLine == $linesTotal - 1) ? true : false;
                            $logged = $this->_logLine($line, $lastLine);
                            if ($logged === true){
                                $entriesProcessed++;
                                $logs[$type]['entries_processed'] = $entriesProcessed;
                                file_put_contents($lineFile, $currentLine);
                            }
                            $currentLine++;
                            $logs[$type]['current_line'] = $currentLine;
                        }
            
                        if ($logFile->eof()){
                            unlink($file);
                            unlink($lineFile);
                        }
                        $logFile = null;
                    }
            
                }
            
                $systemLogArchived = (int)@$logs['system']['entries_processed'];
                $exceptionLogArchived = (int)@$logs['exception']['entries_processed'];
            
                return $helper->__('Number of exception reports archived: %s; number of system log archived: %s; number of exception log archived: %s', $reportsArchived, $systemLogArchived, $exceptionLogArchived );
            }
            
        }
        
        return $helper->__('Number of exception reports archived: %s', $reportsArchived );
    }
            
    /**
     * Log line data
     */
    protected $_logLevel;
    protected $_timestamp;
    protected $_datetime;
    protected $_priority;
    protected $_priorityName;
    protected $_message;
    protected $_title;
    
    /**
     * @param string $line
     * @param string $logLastLine
     * @return bool $logged;
     */
    protected function _logLine($line, $lastLine=false){
        $logged = false;
        if ($line){
            $helper = Mage::helper('storealerts');
            //$format = '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL;
            $format = explode(' ', $line);
            $timestamp = @$format[0];
            $time = strtotime($timestamp);
            if ($time){//start of new entry
                if ($this->_timestamp){//write previous entry
            
                    if ($helper->logPriority($this->_priority)){
                        $logged = $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $this->_message, $this->_title, $this->_datetime);
                    }
                    $this->_timestamp = null;
                    $this->_datetime = null;
                    $this->_priorityName = null;
                    $this->_priority = null;
                    $this->_message = null;
                    $this->_title = null;
                }
            
                $this->_timestamp = $timestamp;
                $this->_datetime = date('Y-m-d H:i:s', strtotime($timestamp));;
                $this->_priorityName = @$format[1];
                $priority = @$format[2];
                $this->_priority = str_replace(array('(',')',':'), '', $priority);
                $messageAr = @array_slice($format,3);
                $this->_message = implode(' ', $messageAr);
            
                $this->_title = trim(substr($this->_message,0,80));
                $this->_title .= (strlen($this->_message)>80) ? '...' : '';
                
            } else {
                $line = trim($line);
                $this->_message .= $line;
            }
            
            //log the last line
            if ($lastLine && $helper->logPriority($this->_priority)){
                $logged = $helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::LOG, $this->_message, $this->_title, $this->_datetime);
            }
            
        }
        		
        return $logged;
    }
    
    /**
     * Archive exception reports in exception table
     * Save report in alert 
     * @return int
     */
    protected function _archiveExceptionReports(){
    	
    	$numExceptions = 0;
    	$preferences = Mage::getModel('storealerts/preference')->getCollection();
    	$preferences->addFieldToFilter('alerts', array('like' => '%exception%'));
    	 
    	if ($preferences->getSize()>0){
    		$limit = ExtensionsStore_StoreAlerts_Model_Exception::LIMIT;
    		$counter = 0;
    		$reportDir = Mage::getBaseDir().DS.'var'.DS.'report';
    		//chmod($reportDir.DS.'*',0664);
    		$files = scandir($reportDir);
    		$helper = Mage::helper('storealerts');
    		$currentDate = date('Y-m-d');
    		 
			if (count($files)>0){
				foreach ($files as $file){
					try {
						$fileToLog = $reportDir.DS.$file;
						if (is_file($fileToLog)){
							if ($counter >= $limit){
								break;
							}
							$counter++;
							$content = file_get_contents($fileToLog);
							$exceptionTime = filemtime($fileToLog);
							$datetime = date('Y-m-d H:i:s', $exceptionTime);
							$exception = Mage::getModel('extensions_store_storealerts/exception')->load($file,'file');
							if (!$exception->getId()){
								$exception->setCreatedAt($datetime);
							}
							$exception->setFile($file)
							->setContent($content)
							->setUpdatedAt($datetime)
							->save();
							if ($exception->getId()){
								$reportAr = unserialize($content);
								$title = $reportAr[0];
								$helper->saveAlert(ExtensionsStore_StoreAlerts_Model_Alert::EXCEPTION, $content, $title, $datetime);
									
								$loggedDir = $reportDir.DS.'logged';
								if (!is_dir($loggedDir)){
									mkdir($loggedDir);
									chmod($loggedDir,0775);
								}
								$loggedFile = $loggedDir.DS.$file;
								rename($fileToLog, $loggedFile);
								$numExceptions++;
							}
						}
					}catch(Exception $e){
						Mage::log($e->getMessage(),Zend_Log::ERR,'extensions_store_storealerts.log');
					}
				}				
			}
    	}
    	
    	return $numExceptions;
    }
}
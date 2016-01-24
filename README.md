Store Alerts 
============
Receive notifications from your Magento website on your mobile device. 
Coming soon to the iOS App Store.

Description
-----------
<img src="md/app.png" width="50px"/>  Our app alerts you to errors on your website. When an error occurs on your website, 
it'll be logged for your review in the admin:

<img src="md/grid.png" />

These errors will be sent to your mobile device as alert Notifications. 

Install our app on your iOS mobile device. Then log in to your website:

<img src="md/login.png" width="300px" />

After you have successfully logged in to your website, you can set your preferences 
for the type of alerts you would like to receive:

<img src="md/preferences.png" width="300px"  />

As you start to receive alerts, you can also view a list of alerts in the app:

<img src="md/alerts.png" width="300px"  />


How to Install
--------------
The iOS app is scheduled for release February 2016. The Magento extension is available now.

Modman:

<pre>
modman clone https://github.com/ExtensionsStore/StoreAlerts.git
</pre>

Composer:

<pre>
{
    "require": {
        "magento-hackathon/magento-composer-installer": "dev-master",
    	"extensions-store/store-alerts" : "dev-master"
    },
    "repositories" : [
    	{
    		"type" : "vcs",
    		"url" : "https://github.com/ExtensionsStore/StoreAlerts.git"
    	}  	
    ],
    "extra": {
        "magento-root-dir": "./html",
        "magento-deploystrategy": "copy",
        "magento-force" : true,
        "with-bootstrap-patch" : false
    }
}

</pre>

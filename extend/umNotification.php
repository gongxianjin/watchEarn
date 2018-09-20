<?php
require_once dirname(__FILE__) . '/' . 'umeng/android/AndroidBroadcast.php';
require_once dirname(__FILE__) . '/' . 'umeng/android/AndroidFilecast.php';
require_once dirname(__FILE__) . '/' . 'umeng/android/AndroidGroupcast.php';
require_once dirname(__FILE__) . '/' . 'umeng/android/AndroidUnicast.php';
require_once dirname(__FILE__) . '/' . 'umeng/android/AndroidCustomizedcast.php';
require_once dirname(__FILE__) . '/' . 'umeng/ios/IOSBroadcast.php';
require_once dirname(__FILE__) . '/' . 'umeng/ios/IOSFilecast.php';
require_once dirname(__FILE__) . '/' . 'umeng/ios/IOSGroupcast.php';
require_once dirname(__FILE__) . '/' . 'umeng/ios/IOSUnicast.php';
require_once dirname(__FILE__) . '/' . 'umeng/ios/IOSCustomizedcast.php';
class umNotification
{
    protected $appkey = NULL;
    protected $appMasterSecret = NULL;
    protected $timestamp = NULL;
    protected $validation_token = NULL;
    function __construct($key, $secret)
    {
        $this->appkey = $key;
        $this->appMasterSecret = $secret;
        $this->timestamp = strval(time());
    }
    function sendAndroidBroadcast($setting)
    {
        try {
            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker", $setting['ticker']);
            $brocast->setPredefinedKeyValue("title", $setting['title']);
            $brocast->setPredefinedKeyValue("text", $setting['text']);
            $brocast->setPredefinedKeyValue("after_open", $setting['after_open']);
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", "true");
            // [optional]Set extra fields
            $brocast->setExtraField("actionName", $setting['actionName']);
            $brocast->setExtraField("actionUrl", $setting['actionUrl']);
            $brocast->setExtraField("actionMethodParam", $setting['actionMethodParam']);
            $brocast->setExtraField("actionMethod", $setting['actionMethod']);
            //print("Sending broadcast notification, please wait...\r\n");
            $re = $brocast->send();
        } catch (Exception $e) {
            $re = $e->getMessage();
        }
        return $re;
    }
    function sendAndroidUnicast($setting)
    {
        try {
            $unicast = new AndroidUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey", $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens", $setting['device_tokens']);
            $unicast->setPredefinedKeyValue("ticker", $setting['ticker']);
            $unicast->setPredefinedKeyValue("title", $setting['title']);
            $unicast->setPredefinedKeyValue("text", $setting['text']);
            $unicast->setPredefinedKeyValue("after_open", $setting['after_open']);
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $unicast->setPredefinedKeyValue("production_mode", "true");
            // Set extra fields
            $unicast->setExtraField("actionName", $setting['actionName']);
            $unicast->setExtraField("actionUrl", $setting['actionUrl']);
            $unicast->setExtraField("actionMethodParam", $setting['actionMethodParam']);
            $unicast->setExtraField("actionMethod", $setting['actionMethod']);
            //print("Sending unicast notification, please wait...\r\n");
            $re = $unicast->send();
        } catch (Exception $e) {
            $re = $e->getMessage();
        }
        return $re;
    }
    function sendAndroidFilecast()
    {
        try {
            $filecast = new AndroidFilecast();
            $filecast->setAppMasterSecret($this->appMasterSecret);
            $filecast->setPredefinedKeyValue("appkey", $this->appkey);
            $filecast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $filecast->setPredefinedKeyValue("ticker", "Android filecast ticker");
            $filecast->setPredefinedKeyValue("title", "Android filecast title");
            $filecast->setPredefinedKeyValue("text", "Android filecast text");
            $filecast->setPredefinedKeyValue("after_open", "go_app");
            //go to app
            print "Uploading file contents, please wait...\r\n";
            // Upload your device tokens, and use '\n' to split them if there are multiple tokens
            $filecast->uploadContents("aa" . "\n" . "bb");
            print "Sending filecast notification, please wait...\r\n";
            $filecast->send();
            print "Sent SUCCESS\r\n";
        } catch (Exception $e) {
            print "Caught exception: " . $e->getMessage();
        }
    }
    function sendAndroidGroupcast()
    {
        try {
            /*
             *  Construct the filter condition:
             *  "where":
             *	{
             *		"and":
             *		[
             *			{"tag":"test"},
             *			{"tag":"Test"}
             *		]
             *	}
             */
            $filter = array("where" => array("and" => array(array("tag" => "test"), array("tag" => "Test"))));
            $groupcast = new AndroidGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey", $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set the filter condition
            $groupcast->setPredefinedKeyValue("filter", $filter);
            $groupcast->setPredefinedKeyValue("ticker", "Android groupcast ticker");
            $groupcast->setPredefinedKeyValue("title", "Android groupcast title");
            $groupcast->setPredefinedKeyValue("text", "Android groupcast text");
            $groupcast->setPredefinedKeyValue("after_open", "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $groupcast->setPredefinedKeyValue("production_mode", "true");
            //print "Sending groupcast notification, please wait...\r\n";
            $re = $groupcast->send();
            //print "Sent SUCCESS\r\n";
        } catch (Exception $e) {
            $re = $e->getMessage();
        }
    }
    function sendAndroidCustomizedcast($setting)
    {
      
        try {
            $customizedcast = new AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
           $customizedcast->setPredefinedKeyValue("alias", $setting['alias']);
            //$customizedcast->setPredefinedKeyValue("device_tokens", $setting['device_tokens']);
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("display_type", $setting['display_type']);
            $customizedcast->setPredefinedKeyValue("alias_type", $setting['alias_type']);
            $customizedcast->setPredefinedKeyValue("ticker", $setting['ticker']);
            $customizedcast->setPredefinedKeyValue("title", $setting['title']);
            $customizedcast->setPredefinedKeyValue("text", $setting['text']);
            $customizedcast->setPredefinedKeyValue("after_open", $setting['after_open']);
            $customizedcast->setPredefinedKeyValue("custom", $setting['custom']);

            $customizedcast->setExtraField("actionName", $setting['actionName']);
            $customizedcast->setExtraField("actionUrl", $setting['actionUrl']);
            // $customizedcast->setExtraField("actionMethodParam", $setting['actionMethodParam']);
            // $customizedcast->setExtraField("actionMethod", $setting['actionMethod']);
            //print "Sending customizedcast notification, please wait...\r\n";
            
            $re = $customizedcast->send();
            // print "Sent SUCCESS\r\n";
        } catch (Exception $e) {

            $re = $e->getMessage();
        }
        return $re;
    }
    function sendAndroidCustomizedcastFileId()
    {
        try {
            $customizedcast = new AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->uploadContents("aa" . "\n" . "bb");
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "xx");
            $customizedcast->setPredefinedKeyValue("ticker", "Android customizedcast ticker");
            $customizedcast->setPredefinedKeyValue("title", "Android customizedcast title");
            $customizedcast->setPredefinedKeyValue("text", "Android customizedcast text");
            $customizedcast->setPredefinedKeyValue("after_open", "go_app");
            print "Sending customizedcast notification, please wait...\r\n";
            $customizedcast->send();
            print "Sent SUCCESS\r\n";
        } catch (Exception $e) {
            print "Caught exception: " . $e->getMessage();
        }
    }
    function sendIOSBroadcast()
    {
        try {
            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $brocast->setPredefinedKeyValue("alert", "IOS 广播测试");
            $brocast->setPredefinedKeyValue("badge", 0);
            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", "false");
            // Set customized fields
            $brocast->setCustomizedField("test", "helloworld");
            print "Sending broadcast notification, please wait...\r\n";
            $brocast->send();
            print "Sent SUCCESS\r\n";
        } catch (Exception $e) {
            print "Caught exception: " . $e->getMessage();
        }
    }
    function sendIOSUnicast($setting)
    {
        try {
            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey", $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens", $setting['device_tokens']);
            $unicast->setPredefinedKeyValue("alert", $setting['alert']);
            $unicast->setPredefinedKeyValue("badge", $setting['badge']);
            $unicast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $unicast->setPredefinedKeyValue("production_mode", $setting['production_mode']);
            // Set customized fields
           // $unicast->setCustomizedField("test", $setting['test']);
            //$unicast->setCustomizedField("actionName", $setting['actionName']);
           //$unicast->setCustomizedField("actionUrl", $setting['actionUrl']);
            $unicast->setCustomizedField("actionMethodParam", $setting['actionMethodParam']);
            $unicast->setCustomizedField("actionMethod", $setting['actionMethod']);
            //print "Sending unicast notification, please wait...\r\n";
            $re = $unicast->send();
            //print "Sent SUCCESS\r\n";
        } catch (Exception $e) {
            //print "Caught exception: " . $e->getMessage();
            $re = $e->getMessage();
        }
        return $re;
    }
    function sendIOSFilecast()
    {
        try {
            $filecast = new IOSFilecast();
            $filecast->setAppMasterSecret($this->appMasterSecret);
            $filecast->setPredefinedKeyValue("appkey", $this->appkey);
            $filecast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $filecast->setPredefinedKeyValue("alert", "IOS 文件播测试");
            $filecast->setPredefinedKeyValue("badge", 0);
            $filecast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $filecast->setPredefinedKeyValue("production_mode", "false");
            print "Uploading file contents, please wait...\r\n";
            // Upload your device tokens, and use '\n' to split them if there are multiple tokens
            $filecast->uploadContents("aa" . "\n" . "bb");
            print "Sending filecast notification, please wait...\r\n";
            $filecast->send();
            print "Sent SUCCESS\r\n";
        } catch (Exception $e) {
            print "Caught exception: " . $e->getMessage();
        }
    }
    function sendIOSGroupcast()
    {
        try {
            /*
             *  Construct the filter condition:
             *  "where":
             *	{
             *		"and":
             *		[
             *			{"tag":"iostest"}
             *		]
             *	}
             */
            $filter = array("where" => array("and" => array(array("tag" => "iostest"))));
            $groupcast = new IOSGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey", $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set the filter condition
            $groupcast->setPredefinedKeyValue("filter", $filter);
            $groupcast->setPredefinedKeyValue("alert", "IOS 组播测试");
            $groupcast->setPredefinedKeyValue("badge", 0);
            $groupcast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $groupcast->setPredefinedKeyValue("production_mode", "false");
            print "Sending groupcast notification, please wait...\r\n";
            $groupcast->send();
            print "Sent SUCCESS\r\n";
        } catch (Exception $e) {
            print "Caught exception: " . $e->getMessage();
        }
    }
    function sendIOSCustomizedcast() {
        try {
            $customizedcast = new IOSCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);

            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias", "xx");
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "xx");
            $customizedcast->setPredefinedKeyValue("alert", "IOS 个性化测试");
            $customizedcast->setPredefinedKeyValue("badge", 0);
            $customizedcast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $customizedcast->setPredefinedKeyValue("production_mode", "false");
            print("Sending customizedcast notification, please wait...\r\n");
            $customizedcast->send();
            print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }
}
<?php
namespace app\library\lib\upush\ios;
use app\library\lib\upush\IOSNotification;

class IOSBroadcast extends IOSNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}
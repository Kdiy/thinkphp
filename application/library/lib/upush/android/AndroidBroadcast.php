<?php
namespace app\library\lib\upush\android;
use app\library\lib\upush\AndroidNotification;

class AndroidBroadcast extends AndroidNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}
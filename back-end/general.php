<?php
/**
 * Created by PhpStorm.
 * User: aritracci
 * Date: 1/29/19
 * Time: 7:45 PM
 */

session_start();

// Retrieve POST data sterilized
function PS($i) {
	if(isset($_POST[$i])) {
		if(is_array($_POST[$i]) && SERVER_PROD) {
			BAN_IP(USER_IP, "Passed array through URL POST (only hackers know how to do this)");
			exit(BANNED_IP_MESSAGE);
		}
		elseif(stripos($_POST[$i], "acunetix")) {
			BAN_IP(USER_IP, "acunetix detected in URL");
			exit(BANNED_IP_MESSAGE);
		}
		else {
			return htmlspecialchars(trim($_POST[$i]), ENT_COMPAT, "UTF-8");
		}
	}

	return NULL;
}


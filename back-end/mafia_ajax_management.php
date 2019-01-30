<?php
/**
 * Created by PhpStorm.
 * User: aritracci
 * Date: 1/29/19
 * Time: 7:22 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/back-end/initialize.php";

function AJAX_RETURN($success = null, $error = null)
{
$OUTPUT = $GLOBALS['OUTPUT'];
	if ($success !== null) {
		$OUTPUT['success'] = $success;
	}
	if ($error !== null) {
		$OUTPUT['error'] = $error;
	}
	exit(json_encode($OUTPUT));
}

$OUTPUT = array
('success' => false
, 'error'  => null
);

$ACTION = PS('action');
switch ($ACTION) {
	case 'create_room':

		$room_name = PS('room_name');

		$find_duplicate_room = new RoomsCollectin(array
		('where' => array
			('where_pdo'    => "room_name = :room_name"
			, ':room_name' => $room_name
			)
		));

		if($find_duplicate_room->RowCount) {
			$OUTPUT['error_code'] = 'duplicate';
			AJAX_RETURN(null, true);
		}

		$user_id = $_SESSION['user_id'];
		$user_array = array();
		$user_array[] = $user_id;

		$room            = new Rooms();
		$room->room_name = $room_name;
		$room->owner_id  = $user_id;
		$room->status    = 'Waiting';
		$room->users     = json_encode($user_array);
		$room->Save();

		$OUTPUT['data'] = $room->Data();
		AJAX_RETURN(true, null);
		break;

	case 'create_user':
		$user_name = PS('user_name');
		$avatar    = PS('avatar');
		$user_id   = $_SESSION['user_id'];

		$find_duplicate_user = new UsersCollection(array
		('where' => array
			('where_pdo'    => "user_id = :user_id"
			, ':user_id' => $user_id
			)
		));

		$user = null;

		if($find_duplicate_user->RowCount) {
			$user = new Users($find_duplicate_user->FirstPK);
			$user->user_name = $user_name;
			$user->avatar    = $avatar;
			$user->Save();
		}else {
			$user = new Users();
			$user->user_id   = $user_id;
			$user->user_name = $user_name;
			$user->avatar    = $avatar;
			$user->Save();
		}

		$OUTPUT['user_name'] = $user_name;
		$OUTPUT['avatar']    = $avatar;
		AJAX_RETURN(true, null);
		break;



}
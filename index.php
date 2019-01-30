<?php session_start(); ?>
<?php
/**
 * Created by PhpStorm.
 * User: aritracci
 * Date: 1/27/19
 * Time: 9:40 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . "/back-end/initialize.php";
$room_name = $_GET['room'];
$user_id = '';
$user_exists = false;
if(!isset($_SESSION['user_id'])) {
	$_SESSION['user_id'] = rand();
}

$user_id = $_SESSION['user_id'];
echo $user_id;

$find_user = new UsersCollection(array
('where' => array
	('where_pdo'    => "user_id = :user_id"
	, ':user_id' => $user_id
	)
));

if($find_user->RowCount){
	$user_name   = $find_user->FirstRow->user_name;
	$avatar      = $find_user->FirstRow->avatar;
	$user_exists = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Mafia</title>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/foundation.css">
	<script src="/js/jquery.js"></script>

</head>
<style>
	body {
		background-color:black;
	}
	#main_wrapper {
		background-color:black;
	}
	.dot {
		height: 80px;
		width: 80px;
		background-color: #bbb;
		border-radius: 50%;
		display: inline-block;
	}
	.avatar {
		position:relative; left:-200px
	}

	.user:hover {
		color:white;
		cursor:pointer;
	}

	.murder {
		color:red;
	}

	#info_screen p {
		font-size:30px;
	}

	.citizen {
		color:green;
	}

	.medic {
		color:deeppink;
	}

	.shooter {
		color:blue;
	}

	fieldset legend {
		font-size:30px;
		color:white;
	}

	#room_name {
		border-width:10px;
	}

	#error {
		font-size:40px;
	}

</style>
<body>
	<div id='main_wrapper' class='row'>
		<p style='font-size:100px'>
			Mafia <? echo $room_name != '' ? 'Room '.$room_name : ''; ?>
		</p>
	</div>

	<div id='room_create_screen' class='row' style='display:block'>
		<div class='small-6 columns'>
			<button class='button large expand success radius'  onclick='create_room()' >Create Room</button>
		</div>
		<div class='small-6 columns'>
			<button class='button large expand secondary radius'>Join Room</button>
		</div>
		<input id='room_name' style='font-size:120px; padding: 80px 20px 80px 40px; border-radius:4px;'  onkeyup='check_room()' placeholder='Room Name'>
		<div data-alert class="alert-box alert radius" id='error' style='display:none'>
			This is a success alert with a radius.
			<a href="#" class="close">&times;</a>
		</div>
	</div>

	<div id='login_screen' class='row' style='display:none'>
		<input id='user_name' onkeyup='check_user_data()'  name='user_name' style='font-size:120px; padding: 80px 20px 80px 40px; border-radius:4px;' placeholder='Your Name'>
		<input id='avatar'    onkeyup='check_user_data()'  name='avatar'    style='font-size:20px;  padding:40px; border-radius:4px;' placeholder='Avatar Link'>
		<button class='button large expand success radius' style='font-size:40px' onclick='create_user()' >Submit</button>
	</div>

	<div id='info_screen' class='row' style='display:none'>
		<div class='small-4 columns'>
			<fieldset>
				<legend>Role</legend>
				<p class='shooter'>...</p>
			</fieldset>
		</div>
		<div class='small-8 columns'>
			<fieldset>
				<legend>Your Turn</legend>
				<p class='citizen'>...</p>
			</fieldset>
		</div>
	</div>

	<div id='queue_screen_wrapper' class='row' style='display:none'>
		<fieldset id='queue_screen'>
			<legend>Players</legend>
			<div id='main_user_wrapper' class='user row'>
				<div class='small-3 columns'>
					<img id='main_user_avatar' class='dot' src='https://ib.hulu.com/user/v3/artwork/36e318dc-3daf-47fb-8219-9e3cb5cd28f2?base_image=b0e25e0f-212a-4e03-bb00-b692fcc9c774&base_image_bucket_name=hummus&size=400x600&format=jpeg'>
				</div>
				<div class='small-8 columns avatar'>
					<p style='font-size:60px'>
						<span id='main_user_name'></span>
					</p>
				</div>
			</div>
			<div class='user row'>
				<div class='small-3 columns'>
					<img class='dot' src='https://ib.hulu.com/user/v3/artwork/36e318dc-3daf-47fb-8219-9e3cb5cd28f2?base_image=b0e25e0f-212a-4e03-bb00-b692fcc9c774&base_image_bucket_name=hummus&size=400x600&format=jpeg'>
				</div>
				<div class='small-8 columns avatar'>
					<p style='font-size:60px'>
						Rahul
					</p>
				</div>
			</div>
			<p id='waiting_p' style='font-size:40px'>Waiting...</p>
		</fieldset>
		<div id='queue_nav'>
			<button class='button large expand warning radius' style='font-size:40px'>Start Game</button>
		</div>

	</div>

</body>

<script>

	user_exists = <? echo $user_exists ?: 0?>;
   room_name = <? echo $room_name ?: 0?>;
	user_name = '<? echo $user_name ?: 0?>';
   avatar = '<? echo $avatar ?: 0?>';

   function create_room() {
		if(!check_room()) {
			 return;
      }
       $.post("/back-end/mafia_ajax_management.php",
           {
               action         : 'create_room'
               , room_name         : room_name
           }, function (result) {
					response = JSON.parse(result);

					if(response.success) {
               	console.log(response);
                   if(!user_exists) {
                       show_login_screen(true);
                   }else {
                       show_user_avatar();
                       show_queue_screen(true);
                       show_info_screen(true);
                   }
                   show_room_create_screen(false);
					}else {
						if(response.error_code == 'duplicate') {
							$('#error').toggle();
							$('#error').text('Pick another room name. Room name already taken');
							$('#error').delay(5000).fadeOut('slow');
                  }
               }
           });
   }

   function create_user() {
       if(!check_user_data()) {
           return;
       }

       user_name_input = document.getElementById('user_name');
       user_name = user_name_input.value;

       avatar_input = document.getElementById('avatar');
       avatar_link = avatar_input.value;

       $.post("/back-end/mafia_ajax_management.php",
           {
               action         : 'create_user'
               , avatar         : avatar_link
					, user_name     : user_name
           }, function (result) {
               console.log(result);
           		response = JSON.parse(result);

               if(response.success) {
                   show_queue_screen(true);
                   show_info_screen(true);
                   show_login_screen(false)

                   show_user_avatar(response);

               }
           });
   }

   function show_user_avatar(response = null) {
     if(response) {
         user_name = response.user_name;
         avatar    = response.avatar;

         $('#main_user_avatar').attr('src', avatar);
         $('#main_user_name').text(user_name);
     }else {
         $('#main_user_avatar').attr('src', avatar);
         $('#main_user_name').text(user_name);
     }
   }

   function check_user_data() {
       user_name_input = document.getElementById('user_name');
       user_name = user_name_input.value;

       avatar_input = document.getElementById('avatar');
       avatar_link = avatar_input.value;

       error = false;

       if(user_name == '' || !user_name) {
           user_name_input.style.borderColor = 'red';
           error = true;
       }else {
           user_name_input.style.borderColor = '#ccc';
       }

       if(avatar_link == '' || !avatar_link) {
           avatar_input.style.borderColor = 'red';
           error = true;
       }else {
           avatar_input.style.borderColor = '#ccc';
       }

       return !error;
   }

   function show_login_screen(flg) {
		if(flg) {
          $('#login_screen').css('display', 'block');
      }else {
          $('#login_screen').css('display', 'none');
      }
   }

   function show_room_create_screen(flg) {
       if(flg) {
           $('#room_create_screen').css('display', 'block');
       }else {
           $('#room_create_screen').css('display', 'none');
       }
   }

   function show_queue_screen(flg) {
       if(flg) {
           $('#queue_screen_wrapper').css('display', 'block');
       }else {
           $('#queue_screen_wrapper').css('display', 'none');
       }
   }

   function show_info_screen(flg) {
       if(flg) {
           $('#info_screen').css('display', 'block');
       }else {
           $('#info_screen').css('display', 'none');
       }
   }

   function check_room() {
       room_input = document.getElementById('room_name');
       room_name = room_input.value;
       if(room_name == '' || !room_name) {
           room_input.style.borderColor = 'red';
           return false;
       }else {
           room_input.style.borderColor = '#ccc';
           return true;
       }
   }
</script>

</html>
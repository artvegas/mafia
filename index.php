<?php
/**
 * Created by PhpStorm.
 * User: aritracci
 * Date: 1/27/19
 * Time: 9:40 PM
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Mafia</title>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/foundation.css">
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

</style>
<body>
	<div id='main_wrapper' class='row'>
		<p style='font-size:100px'>
			Mafia // Room 132
		</p>
	</div>

	<div id='login_screen' class='row' style='display:none'>
		<input style='font-size:120px; padding: 80px 20px 80px 40px; border-radius:4px;' placeholder='Your Name'>
		<input style='font-size:20px;  padding:40px; border-radius:4px;' placeholder='Avatar Link'>
		<button class='button large expand success radius' style='font-size:40px'>Submit</button>
	</div>

	<div id='info_screen' class='row'>
		<div class='small-4 columns'>
			<fieldset>
				<legend>Role</legend>
				<p class='shooter'>Nurse</p>
			</fieldset>
		</div>
		<div class='small-8 columns'>
			<fieldset>
				<legend>Your Turn</legend>
				<p class='murder'>Pick someone to murder</p>
			</fieldset>
		</div>
	</div>

	<div class='row'>
		<fieldset id='queue_screen'>
			<legend>Players</legend>
			<div class='user row'>
				<div class='small-3 columns'>
					<img class='dot' src='https://ib.hulu.com/user/v3/artwork/36e318dc-3daf-47fb-8219-9e3cb5cd28f2?base_image=b0e25e0f-212a-4e03-bb00-b692fcc9c774&base_image_bucket_name=hummus&size=400x600&format=jpeg'>
				</div>
				<div class='small-8 columns avatar'>
					<p style='font-size:60px'>
						Aritra
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
		</fieldset>
		<div id='queue_nav'>
			<button class='button large expand warning radius' style='font-size:40px'>Start Game</button>
		</div>

	</div>

</body>
</html>
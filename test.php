<?php

require_once 'uploadclass.php';

if (isset($_FILES['avatar']))
{
	$upload = new Upload('avatar');
	$upload->set_upload_path('uploaded');
	$upload->set_max_size('300KB'); // Another example: 300B, 300KB, 30MB, 3GB, 3TB
	$upload->set_allowed_types('jpg|png|gif');
	$upload->set_name('My_avatar.'.$upload->get_ext()); // New name extension can set manually, example: $upload->set_name('My_avatar.png');

	if ($upload->run() !== false)
	{
		echo 'Your Avatar Successfully Uploaded<br/>';
	}
	else
	{
		echo '<pre>';
		print_r($upload->get_errors());
		echo '</pre>';
	}
}
?>

<form action="test.php" method="post" enctype="multipart/form-data">
<input name="avatar" type="file" />
<input name="upload" type="submit" value="Upload" />
</form>

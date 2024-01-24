<?php
if (isset($_POST['button']))
    {
         shell_exec('sudo ./euro/flash_euro.sh');
		 header("Location:finish.php");

    }
?>


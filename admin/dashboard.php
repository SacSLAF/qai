<!DOCTYPE html>
<html lang="en">

<?php
session_start();
include "template/head.php";
?>

<body>

	<?php
	include "template/preloader.php";
	?>



	<div id="main-wrapper">


		<?php
		
		include "template/nav.php";
		// include "template/chatbox.php";
		include "template/header.php";
		?>

	
		<?php
		include "template/desnav.php";
		?>


		<div class="content-body">
			<!-- row -->
			<div class="container-fluid">
				<div class="row">
					<?php
					// var_dump($_SESSION['admin_id']);
					include "section/mainbox.php";
					?>

				</div>
			</div>
		</div>

		<?php
		include "template/footer.php";
		?>



	</div>

	
		<?php
		include "template/foot.php";
		?>


</body>

</html>
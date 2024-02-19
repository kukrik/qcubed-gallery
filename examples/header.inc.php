<?php
// This example header.inc.php is intended to be modfied for your application.
use QCubed as Q;
?>
<!DOCTYPE html>
<html>
<head>

	<meta charset="<?php echo(QCUBED_ENCODING); ?>"/>
	<meta content="text/html"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<?php if (isset($strPageTitle)){ ?><title><?php _p($strPageTitle); ?></title><?php } ?>

	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700&subset=all" rel="stylesheet" type="text/css"/>
	<link href="../../../../project/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
	<link href="../assets/css/font-awesome.css" rel="stylesheet"/>
	<link href="../assets/css/qcubed.gallery.css" rel="stylesheet"/>
	<link href="../assets/css/toastr.css" rel="stylesheet"/>
	<link href="../assets/css/toastr.fontawesome.css" rel="stylesheet"/>
	<link href="../../select2/assets/css/select2.css" rel="stylesheet"/>
	<link href="../../select2/assets/css/select2-bootstrap.css" rel="stylesheet"/>
	<link href="../assets/css/select2-web-vauu.css" rel="stylesheet"/>
	<link href="../assets/css/awesome-bootstrap-checkbox.css" rel="stylesheet"/>
    <link href="/qcubed-4/vendor/kukrik/bootstrap-filecontrol/assets/css/jquery.fileupload.css" rel="stylesheet" />
    <link href="/qcubed-4/vendor/kukrik/bootstrap-filecontrol/assets/css/jquery.fileupload-ui.css" rel="stylesheet" />

    <style>
        .preview img {
            height: 90px;
            width: 90px;
            object-fit: cover;
            object-position: 100% 0;
        }
		.select2-container--web-vauu .select2-results > .select2-results__options {
			height: auto;
			max-height: none;
			overflow-y: auto;
		}
		[type="search"]::-webkit-search-cancel-button,
		[type="search"]::-webkit-search-decoration {
			-webkit-appearance: none;
		}
	</style>

</head>
	<body>
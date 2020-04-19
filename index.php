<?php
	namespace Index;
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php';
	use \shgysk8zer0\PHPAPI\{CSP};
	use \Parsedown;
	use const \Consts\README;

	$csp = new CSP("'self'");
	$csp->imgSrc('*');
	$csp->styleSrc('https://cdn.kernvalley.us');
	$csp->scriptSrc("'none'");
	$csp->fontSrc('https://cdn.kernvalley.us');
	$csp->send();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" class="border-box smooth-scroll">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width" />
		<link rel="icon" href="https://kernvalley.us/favicon.ico" />
		<link rel="stylesheet" href="https://cdn.kernvalley.us/css/core-css/rem.css" media="all" />
		<link rel="stylesheet" href="https://cdn.kernvalley.us/css/core-css/element.css" media="all" />
		<link rel="stylesheet" href="https://cdn.kernvalley.us/css/core-css/class-rules.css" media="all" />
		<link rel="stylesheet" href="https://cdn.kernvalley.us/css/core-css/theme/default/index.css" media="all" />
		<link rel="stylesheet" href="https://cdn.kernvalley.us/css/core-css/fonts.css" media="all" />
		<link rel="stylesheet" href="https://cdn.kernvalley.us/css/core-css/scrollbar.css" media="all" />
		<title>KernValley.US API</title>
	</head>
	<body class="background-primary color-default font-main">
		<main class="card shadow">
			<?= Parsedown::instance()->text(file_get_contents(README)) ?>
		</main>
	</body>
</html>

<?
	require_once("global.inc.php");

	$path = realpath( $_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER['SCRIPT_URL'] );


	require_once("lib/lib_directory_content_handler.php");
	$dc = new Directory_Content_Handler($path);
	$dc->run();

?>

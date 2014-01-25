<?
	require_once("global.inc.php");

   $path_method = 1; // script uri
   $path_method = 2; // request uri
   // $path_method = -1;      // debug

   if ($path_method == 1) {
	   $path = realpath( $_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER['SCRIPT_URL'] );

   } else if ($path_method == 2) {

	   $path = realpath( $_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER['REQUEST_URI'] );

   } else if ($path_method == -1) { // debug

      echo "<pre>";
      print_r($_SERVER);
      print_r($_REQUEST);
      echo "</pre>";

   }

   echo "<h4>$path</h4>";

	$dh = new Directory_Listing_Handler($path);
	$dh->run();

?>

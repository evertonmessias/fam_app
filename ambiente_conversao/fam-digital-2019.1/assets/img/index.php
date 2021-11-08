<?php
require_once ('minifier.php');

$js = file_get_contents($_GET['_file_']);

$minifiedCode = \JShrink\Minifier::minify($js);

// Disable YUI style comment preservation.
$minifiedCode = \JShrink\Minifier::minify($js, array('flaggedComments' => false));

// Enable GZip encoding.
ob_start("ob_gzhandler");
// Enable caching
//header('Cache-Control: public');
// Expire in one day
//header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
// Set the correct MIME type, because Apache won't set it for us
header("Content-type: text/javascript");
// Write everything out
echo($minifiedCode);
?>
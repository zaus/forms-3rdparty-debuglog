<?php
/*

Plugin Name: Forms: 3rd-Party Debug Log
Plugin URI: https://github.com/zaus/forms-3rdparty-debuglog
Description: Log debug messages to an upload folder instead of email
Author: zaus
Version: 0.1
Author URI: http://drzaus.com
Changelog:
	0.1	concept
*/

class Forms3rdPartyDebug { 

	#region =============== CONSTANTS AND VARIABLE NAMES ===============
	
	/**
	 * Base plugin name (for hooks)
	 * @var string
	 */
	const B = 'Forms3rdPartyIntegration';
	/**
	 * Version of current plugin -- match it to the comment
	 * @var string
	 */
	const pluginVersion = '0.1';
	
	/**
	 * Namespace the given key
	 * @param string $key the key to namespace
	 * @return the namespaced key
	 */
	public function N($key = false) {
		// nothing provided, return namespace
		if( ! $key || empty($key) ) { return self::B; }
		return sprintf('%s_%s', self::B, $key);
	}

	#endregion =============== CONSTANTS AND VARIABLE NAMES ===============
	
	
	#region =============== CONSTRUCTOR and INIT (admin, regular) ===============
	
	// php5 constructor must come first for 'strict standards' -- http://wordpress.org/support/topic/redefining-already-defined-constructor-for-class-wp_widget

	function __construct() {
		add_action( $this->N('debug_message'), array( &$this, 'debug_message' ), 10, 5 );
	} // function

	#endregion =============== CONSTRUCTOR and INIT (admin, regular) ===============
	
	#region =============== HEADER/FOOTER -- scripts and styles ===============
	
	/**
	 * Do something with the debug message contents and return `false` if we should bypass regular mail attempt
	 */
	function debug_message($passthrough, $service, $post, $submission, $response){
		// https://geek.hellyer.kiwi/2014/06/27/saving-css-files-uploads-folder/
		
		$upload = wp_upload_dir();
		
		// custom directory, lazy -- is this safe?
		// maybe an admin setting to 'obfuscate' the directory?
		$dir = isset($_GET['f3p_debug']) ? $_GET['f3p_debug'] : '';
		
		$dir = trailingslashit($upload['basedir']) . trailingslashit(__CLASS__ . '/' . $dir);
		
		// create if not there, 'fail' if we can't make it
		if(!file_exists($dir)) {
			if(!wp_mkdir_p($dir)) { return true; }
			
			// TODO: add some index.php / .htaccess protection so ppl can't just browse...but isn't that the point?
			file_put_contents("{$dir}index.html", '');
		}

		file_put_contents("{$dir}f3p_debug.log", print_r(array(
			'post' => $post,
			'submission' => $submission,
			'response' => $response,
			'service' => $service
		), true) /* , FILE_APPEND */);
		
		// don't send the email = false; send the email too = true;
		return $passthrough;
	}//---	function add_admin_headers
	
}//end class

// engage
new Forms3rdPartyDebug;


/*
// some servers need at least one 'sacrificial' `error_log` call to make _log call work???

error_log('f3p-after-declare:' . $_SERVER["REQUEST_URI"]);

if(!function_exists('_log')) {
function _log($args) {
	$args = func_get_args();
	error_log( print_r($args, true) );
}
}
*/

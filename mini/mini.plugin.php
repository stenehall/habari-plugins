<?php

	set_include_path(dirname(__FILE__). '/min/lib' . PATH_SEPARATOR . get_include_path());

	require_once 'Minify/Source.php';
	require_once 'Minify/HTML.php'; 
	require_once 'Minify/CSS.php'; 
	require_once 'Minify/HTML.php'; 
	require_once 'Minify.php';
	require_once 'Minify/Cache/File.php';


class Mini extends Plugin {

	private static $cache_name = 'minify';
	private static $stack;

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
	 	Update::add( 'Minify', '', $this->info->version );
	}

	public function action_template_header() {


		$modified = Stack::get_sorted_stack('template_header_javascript');
		foreach( $modified as $key => $value ) {			
			Stack::remove('template_header_javascript', $key);
		}
		
		Stack::add('template_header_javascript', Site::get_url('user') . "/files/minified.js", 'Minified');
		
		if ( !Cache::has( self::$cache_name . '_js' ) ) {
			$js_stack = array();
			foreach( $modified as $js ) {
				$js_stack[] = Site::get_path('base') . str_replace(Site::get_url('habari') . '/', '', $js);
			}
			$options = array(
			    'files' => $js_stack,
			    'encodeOutput' => false,
	   		    'quiet' => true,
			    'maxAge' => 86400,
			    
			);
			$result = Minify::serve('Files', $options);
			file_put_contents( site::get_dir('user') . '/files/minified.js', $result['content']);
			Cache::set( self::$cache_name . '_js', 'true' );
		}

		/* CSS */

		$modified = Stack::get_sorted_stack('template_stylesheet');
		$tmp = array();
		foreach( $modified as $key => $value ) {			
			$tmp[] = $value[0];
			Stack::remove('template_stylesheet', $key);
		}
		Stack::add('template_stylesheet', array( Site::get_url('user') . "/files/minified.css", 'screen'), 'style' );

		if ( !Cache::has( self::$cache_name . '_css' ) ) {
			$css_stack = array();
			foreach( $tmp as $css ) {
				$css_stack[] = Site::get_path('base') . str_replace(Site::get_url('habari') . '/', '', $css);
			}
			$options = array(
			    'files' => $css_stack,
			    'encodeOutput' => false,
	   		    'quiet' => true,
			    'maxAge' => 86400,
			    
			);
			// handle request
			$result = Minify::serve('Files', $options);
			file_put_contents( site::get_dir('user') . '/files/minified.css', $result['content']);

			Cache::set( self::$cache_name . '_css', 'true' );
		}

	}

	public function filter_final_output( $buffer )
	{
		return Minify_HTML::minify( $buffer );
	}
}





?>
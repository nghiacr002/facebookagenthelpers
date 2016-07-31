<?php 
spl_autoload_extensions(".php");
spl_autoload_register('system_autoloader');
function system_autoloader($class)
{
	$prefix = 'Facebook\\';
	// base directory for the namespace prefix
	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}
	// get the relative class name
	$relative_class = substr($class, $len);

	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = APP_PATH."Facebook".APP_DS . str_replace('\\', '/', $relative_class) . '.php';

	// if the file exists, require it
	if (file_exists($file)) {
		require $file;
	}
}

<?php
/**
 * Material3d
 *
 * Plugin Name: Material3d
 * Plugin URI:  https://wordpress.org/plugins/material3d
 * Description: Material3d is a platform provides a WYSIWYG way to construct and run interactive 3d scenes on web browsers with VR (Virtual Reality) device support.
 * Version:     1.0.0
 * Author:      Tonicraftor
 * Author URI:  https://material3d.net/about.html
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: m3dwp
 * Domain Path: /languages
 * Requires at least: 4.7
 * Requires PHP: 7.0
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

define( 'M3DWP_VERSION', '1.0.0' );
define('M3DWP_URL', plugin_dir_url( __FILE__ ));
define('M3DWP_PATH', plugin_dir_path( __FILE__ ));
define('M3DWP_PLUGIN_BASENAME', plugin_basename( __FILE__ ));
define('M3D_NET_HOME', 'https://material3d.net/');

require_once(M3DWP_PATH.'classes/class-material3dwp.php');

function init_material3d(){
    $m3dwp = new Material3dWP();
    //activate hook
    register_activation_hook( __FILE__, array($m3dwp, 'activate_hook' ) );
    register_uninstall_hook( __FILE__, array($m3dwp, 'uninstall_hook' ) );
}

init_material3d();

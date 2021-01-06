<?php
/**
 * Material3d
 *
 * Plugin Name: Material3d
 * Plugin URI:  https://material3d.net/wp/
 * Description: Material3d is a platform for users to create and run 3d scenes on web browsers. It provides a visualized and WYSIWYG way to construct 3d scenes with objects, lights, cameras, textures, materials, animations, force fields and user interactions (mouse click, screen touch, and so on). It also provides an environment to run interactive 3d scenes on web browsers. In addition, VR (Virtual Reality) device is basically supported by Material3d.
 * Version:     1.0.0
 * Author:      Tony Luo
 * Author URI:  https://material3d.net/about.html
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: m3dwp
 * Domain Path: /languages
 * Requires at least: 4.9
 * Requires PHP: 5.2.4
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

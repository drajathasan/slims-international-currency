<?php
/**
 * Plugin Name: International Currency
 * Plugin URI: http://github.com/drajathasan/slims-international-currency
 * Description: Config SLiMS to support International Currency with decimal format. in Circulation module.
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://github.com/drajathasan/
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering menus or hook
$plugin->registerMenu("system", "Currency Settings", __DIR__ . "/index.php");
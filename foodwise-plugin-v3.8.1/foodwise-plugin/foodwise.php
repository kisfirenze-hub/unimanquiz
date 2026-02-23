<?php
/**
 * Plugin Name: FoodWise Quiz Manager
 * Plugin URI: https://foodwise.l-app.net
 * Description: Sistema completo per la gestione di quiz sulla riduzione dello spreco alimentare con autenticazione tramite codici, import CSV e visualizzazione report.
 * Version: 3.8.1
 * Author: LambdaSoft di Russo Luca
 * Author URI: https://www.lambdasoft.it
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: foodwise
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Se questo file viene chiamato direttamente, abort.
if (!defined('WPINC')) {
    die;
}

// Versione del plugin
define('FOODWISE_VERSION', '3.8.1');
define('FOODWISE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FOODWISE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FOODWISE_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Caricamento delle classi principali del plugin
 */
require_once FOODWISE_PLUGIN_DIR . 'includes/class-foodwise-database.php';
require_once FOODWISE_PLUGIN_DIR . 'includes/class-foodwise-auth.php';
require_once FOODWISE_PLUGIN_DIR . 'includes/class-foodwise-csv-import.php';
require_once FOODWISE_PLUGIN_DIR . 'includes/class-foodwise-quiz.php';
require_once FOODWISE_PLUGIN_DIR . 'includes/class-foodwise-categories.php';
require_once FOODWISE_PLUGIN_DIR . 'admin/class-foodwise-admin.php';
require_once FOODWISE_PLUGIN_DIR . 'public/class-foodwise-public.php';

/**
 * Bootstrap principale del plugin
 */
class FoodWise_Plugin {
    /**
     * @var FoodWise_Admin
     */
    private $admin;

    /**
     * @var FoodWise_Public
     */
    private $public;

    public function __construct() {
        $this->admin = new FoodWise_Admin();
        $this->public = new FoodWise_Public();
    }

    public function register_hooks() {
        register_activation_hook(__FILE__, array(__CLASS__, 'activate'));
        register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));

        add_action('init', array($this, 'init'));
    }

    public static function activate() {
        require_once FOODWISE_PLUGIN_DIR . 'includes/class-foodwise-activator.php';
        FoodWise_Activator::activate();
    }

    public static function deactivate() {
        require_once FOODWISE_PLUGIN_DIR . 'includes/class-foodwise-deactivator.php';
        FoodWise_Deactivator::deactivate();
    }

    public function init() {
        $this->admin->init();
        $this->public->init();
    }
}

// Avvia il plugin
$foodwise_plugin = new FoodWise_Plugin();
$foodwise_plugin->register_hooks();

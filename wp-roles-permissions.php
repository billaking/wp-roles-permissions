<?php
/**
 * Plugin Name: WP Roles & Permissions
 * Plugin URI: https://github.com/billaking/wp-roles-permissions
 * Description: Advanced role and permission management for WordPress. Create custom roles, assign roles to users, and restrict content access based on roles.
 * Version: 1.0.0
 * Author: billaking
 * Author URI: https://github.com/billaking
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-roles-permissions
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_ROLES_PERMISSIONS_VERSION', '1.0.0');
define('WP_ROLES_PERMISSIONS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_ROLES_PERMISSIONS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once WP_ROLES_PERMISSIONS_PLUGIN_DIR . 'includes/class-role-manager.php';
require_once WP_ROLES_PERMISSIONS_PLUGIN_DIR . 'includes/class-content-restriction.php';
require_once WP_ROLES_PERMISSIONS_PLUGIN_DIR . 'includes/class-admin-interface.php';

/**
 * Main plugin class
 */
class WP_Roles_Permissions {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Role Manager instance
     */
    public $role_manager;
    
    /**
     * Content Restriction instance
     */
    public $content_restriction;
    
    /**
     * Admin Interface instance
     */
    public $admin_interface;
    
    /**
     * Get single instance of the class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Initialize components
        $this->role_manager = new WP_Roles_Permissions_Role_Manager();
        $this->content_restriction = new WP_Roles_Permissions_Content_Restriction();
        
        // Initialize admin interface only in admin area
        if (is_admin()) {
            $this->admin_interface = new WP_Roles_Permissions_Admin_Interface();
        }
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options if needed
        if (!get_option('wp_roles_permissions_version')) {
            update_option('wp_roles_permissions_version', WP_ROLES_PERMISSIONS_VERSION);
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }
}

/**
 * Initialize the plugin
 */
function wp_roles_permissions_init() {
    return WP_Roles_Permissions::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'wp_roles_permissions_init');

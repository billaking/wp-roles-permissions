<?php
/**
 * Role Manager Class
 * Handles creation, editing, and deletion of custom roles
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Roles_Permissions_Role_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize hooks
     */
    public function init() {
        // Add custom capabilities to administrator role
        $this->add_admin_capabilities();
    }
    
    /**
     * Add custom capabilities to administrator role
     */
    private function add_admin_capabilities() {
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_custom_roles');
            $admin_role->add_cap('assign_roles_to_content');
        }
    }
    
    /**
     * Create a new role
     *
     * @param string $role_slug Role slug
     * @param string $role_name Display name for the role
     * @param array $capabilities Array of capabilities
     * @return WP_Role|WP_Error Role object on success, WP_Error on failure
     */
    public function create_role($role_slug, $role_name, $capabilities = array()) {
        // Validate inputs
        if (empty($role_slug) || empty($role_name)) {
            return new WP_Error('invalid_role', __('Role slug and name are required.', 'wp-roles-permissions'));
        }
        
        // Check if role already exists
        if (get_role($role_slug)) {
            return new WP_Error('role_exists', __('Role already exists.', 'wp-roles-permissions'));
        }
        
        // Create the role
        $role = add_role($role_slug, $role_name, $capabilities);
        
        if (!$role) {
            return new WP_Error('role_creation_failed', __('Failed to create role.', 'wp-roles-permissions'));
        }
        
        // Store custom role in options for tracking
        $custom_roles = get_option('wp_roles_permissions_custom_roles', array());
        $custom_roles[$role_slug] = array(
            'name' => $role_name,
            'capabilities' => $capabilities,
            'created' => current_time('mysql')
        );
        update_option('wp_roles_permissions_custom_roles', $custom_roles);
        
        return $role;
    }
    
    /**
     * Update an existing role
     *
     * @param string $role_slug Role slug
     * @param string $role_name New display name
     * @param array $capabilities New capabilities
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function update_role($role_slug, $role_name, $capabilities = array()) {
        // Validate inputs
        if (empty($role_slug)) {
            return new WP_Error('invalid_role', __('Role slug is required.', 'wp-roles-permissions'));
        }
        
        // Get the role
        $role = get_role($role_slug);
        if (!$role) {
            return new WP_Error('role_not_found', __('Role not found.', 'wp-roles-permissions'));
        }
        
        // Prevent editing WordPress default roles
        $default_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        if (in_array($role_slug, $default_roles)) {
            return new WP_Error('cannot_edit_default', __('Cannot edit default WordPress roles.', 'wp-roles-permissions'));
        }
        
        // Remove all existing capabilities
        foreach ($role->capabilities as $cap => $enabled) {
            $role->remove_cap($cap);
        }
        
        // Add new capabilities
        foreach ($capabilities as $cap => $enabled) {
            if ($enabled) {
                $role->add_cap($cap);
            }
        }
        
        // Update custom role in options
        $custom_roles = get_option('wp_roles_permissions_custom_roles', array());
        if (isset($custom_roles[$role_slug])) {
            $custom_roles[$role_slug]['name'] = $role_name;
            $custom_roles[$role_slug]['capabilities'] = $capabilities;
            $custom_roles[$role_slug]['updated'] = current_time('mysql');
            update_option('wp_roles_permissions_custom_roles', $custom_roles);
        }
        
        // Update role display name in global
        global $wp_roles;
        $wp_roles->roles[$role_slug]['name'] = $role_name;
        update_option($wp_roles->role_key, $wp_roles->roles);
        
        return true;
    }
    
    /**
     * Delete a custom role
     *
     * @param string $role_slug Role slug to delete
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function delete_role($role_slug) {
        // Validate input
        if (empty($role_slug)) {
            return new WP_Error('invalid_role', __('Role slug is required.', 'wp-roles-permissions'));
        }
        
        // Prevent deleting WordPress default roles
        $default_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        if (in_array($role_slug, $default_roles)) {
            return new WP_Error('cannot_delete_default', __('Cannot delete default WordPress roles.', 'wp-roles-permissions'));
        }
        
        // Check if role exists
        if (!get_role($role_slug)) {
            return new WP_Error('role_not_found', __('Role not found.', 'wp-roles-permissions'));
        }
        
        // Get users with this role and reassign them
        $users = get_users(array('role' => $role_slug));
        foreach ($users as $user) {
            $user_obj = new WP_User($user->ID);
            $user_obj->remove_role($role_slug);
            // Assign subscriber role as default
            if (empty($user_obj->roles)) {
                $user_obj->add_role('subscriber');
            }
        }
        
        // Remove the role
        remove_role($role_slug);
        
        // Remove from custom roles tracking
        $custom_roles = get_option('wp_roles_permissions_custom_roles', array());
        if (isset($custom_roles[$role_slug])) {
            unset($custom_roles[$role_slug]);
            update_option('wp_roles_permissions_custom_roles', $custom_roles);
        }
        
        return true;
    }
    
    /**
     * Get all custom roles created by this plugin
     *
     * @return array Array of custom roles
     */
    public function get_custom_roles() {
        return get_option('wp_roles_permissions_custom_roles', array());
    }
    
    /**
     * Get all available WordPress capabilities
     *
     * @return array Array of capabilities
     */
    public function get_all_capabilities() {
        global $wp_roles;
        
        $capabilities = array();
        foreach ($wp_roles->roles as $role) {
            if (isset($role['capabilities'])) {
                $capabilities = array_merge($capabilities, array_keys($role['capabilities']));
            }
        }
        
        return array_unique($capabilities);
    }
    
    /**
     * Assign role to user
     *
     * @param int $user_id User ID
     * @param string $role_slug Role slug
     * @param bool $replace Whether to replace existing roles
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function assign_role_to_user($user_id, $role_slug, $replace = false) {
        // Validate inputs
        if (empty($user_id) || empty($role_slug)) {
            return new WP_Error('invalid_input', __('User ID and role are required.', 'wp-roles-permissions'));
        }
        
        // Check if role exists
        if (!get_role($role_slug)) {
            return new WP_Error('role_not_found', __('Role not found.', 'wp-roles-permissions'));
        }
        
        // Get user
        $user = get_userdata($user_id);
        if (!$user) {
            return new WP_Error('user_not_found', __('User not found.', 'wp-roles-permissions'));
        }
        
        // Assign role
        if ($replace) {
            $user->set_role($role_slug);
        } else {
            $user->add_role($role_slug);
        }
        
        return true;
    }
    
    /**
     * Remove role from user
     *
     * @param int $user_id User ID
     * @param string $role_slug Role slug
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function remove_role_from_user($user_id, $role_slug) {
        // Validate inputs
        if (empty($user_id) || empty($role_slug)) {
            return new WP_Error('invalid_input', __('User ID and role are required.', 'wp-roles-permissions'));
        }
        
        // Get user
        $user = get_userdata($user_id);
        if (!$user) {
            return new WP_Error('user_not_found', __('User not found.', 'wp-roles-permissions'));
        }
        
        // Remove role
        $user->remove_role($role_slug);
        
        // If user has no roles, assign subscriber
        if (empty($user->roles)) {
            $user->add_role('subscriber');
        }
        
        return true;
    }
}

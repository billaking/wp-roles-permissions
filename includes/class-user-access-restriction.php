<?php
/**
 * User Access Restriction Class
 * Prevents non-administrators from managing administrator accounts
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Roles_Permissions_User_Access_Restriction {
    
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
        // Prevent non-admins from viewing administrators in user lists
        add_action('pre_get_users', array($this, 'exclude_admin_users_from_list'));
        
        // Prevent non-admins from editing administrators
        add_filter('user_has_cap', array($this, 'prevent_admin_user_edit'), 10, 4);
        
        // Prevent non-admins from deleting administrators
        add_filter('user_has_cap', array($this, 'prevent_admin_user_delete'), 10, 4);
        
        // Show admin notice when trying to edit an administrator
        add_action('admin_notices', array($this, 'show_admin_edit_notice'));
    }
    
    /**
     * Exclude administrator users from user lists for non-administrators
     *
     * @param WP_User_Query $query User query object
     */
    public function exclude_admin_users_from_list($query) {
        // Only apply in admin area
        if (!is_admin()) {
            return;
        }
        
        // Skip if current user is an administrator
        if (current_user_can('administrator')) {
            return;
        }
        
        // Only apply to users with list_users or edit_users capability
        if (!current_user_can('list_users') && !current_user_can('edit_users')) {
            return;
        }
        
        // Exclude users with administrator role
        $query->query_vars['role__not_in'] = array('administrator');
    }
    
    /**
     * Prevent non-administrators from editing administrator accounts
     *
     * @param array $allcaps All capabilities of the user
     * @param array $caps Required capabilities
     * @param array $args Arguments
     * @param WP_User $user User object
     * @return array Modified capabilities
     */
    public function prevent_admin_user_edit($allcaps, $caps, $args, $user) {
        // Check if this is an edit_user capability check
        if (!isset($args[0]) || $args[0] !== 'edit_user') {
            return $allcaps;
        }
        
        // Skip if current user is an administrator
        if (isset($allcaps['administrator']) && $allcaps['administrator']) {
            return $allcaps;
        }
        
        // Check if target user exists
        if (!isset($args[2])) {
            return $allcaps;
        }
        
        $target_user_id = $args[2];
        $target_user = get_userdata($target_user_id);
        
        // If target user doesn't exist, return
        if (!$target_user) {
            return $allcaps;
        }
        
        // Check if target user is an administrator
        if (in_array('administrator', $target_user->roles)) {
            // Remove edit_users capability for this specific check
            $allcaps['edit_users'] = false;
        }
        
        return $allcaps;
    }
    
    /**
     * Prevent non-administrators from deleting administrator accounts
     *
     * @param array $allcaps All capabilities of the user
     * @param array $caps Required capabilities
     * @param array $args Arguments
     * @param WP_User $user User object
     * @return array Modified capabilities
     */
    public function prevent_admin_user_delete($allcaps, $caps, $args, $user) {
        // Check if this is a delete_user capability check
        if (!isset($args[0]) || $args[0] !== 'delete_user') {
            return $allcaps;
        }
        
        // Skip if current user is an administrator
        if (isset($allcaps['administrator']) && $allcaps['administrator']) {
            return $allcaps;
        }
        
        // Check if target user exists
        if (!isset($args[2])) {
            return $allcaps;
        }
        
        $target_user_id = $args[2];
        $target_user = get_userdata($target_user_id);
        
        // If target user doesn't exist, return
        if (!$target_user) {
            return $allcaps;
        }
        
        // Check if target user is an administrator
        if (in_array('administrator', $target_user->roles)) {
            // Remove delete_users capability for this specific check
            $allcaps['delete_users'] = false;
        }
        
        return $allcaps;
    }
    
    /**
     * Show admin notice when trying to access an administrator's profile
     */
    public function show_admin_edit_notice() {
        global $pagenow;
        
        // Only on user-edit.php page
        if ($pagenow !== 'user-edit.php') {
            return;
        }
        
        // Skip if current user is an administrator
        if (current_user_can('administrator')) {
            return;
        }
        
        // Check if user parameter exists
        if (!isset($_GET['user_id'])) {
            return;
        }
        
        $target_user_id = intval($_GET['user_id']);
        $target_user = get_userdata($target_user_id);
        
        // If target user doesn't exist, return
        if (!$target_user) {
            return;
        }
        
        // Check if target user is an administrator
        if (in_array('administrator', $target_user->roles)) {
            echo '<div class="notice notice-error"><p>';
            echo __('You do not have permission to edit administrator accounts.', 'wp-roles-permissions');
            echo '</p></div>';
        }
    }
}

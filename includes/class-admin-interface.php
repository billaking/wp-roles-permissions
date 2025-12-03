<?php
/**
 * Admin Interface Class
 * Handles admin UI for role management
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Roles_Permissions_Admin_Interface {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_init', array($this, 'handle_role_actions'));
        add_action('admin_init', array($this, 'handle_user_role_actions'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Roles & Permissions', 'wp-roles-permissions'),
            __('Roles & Permissions', 'wp-roles-permissions'),
            'manage_options',
            'wp-roles-permissions',
            array($this, 'render_roles_page'),
            'dashicons-admin-users',
            30
        );
        
        add_submenu_page(
            'wp-roles-permissions',
            __('Manage Roles', 'wp-roles-permissions'),
            __('Manage Roles', 'wp-roles-permissions'),
            'manage_options',
            'wp-roles-permissions',
            array($this, 'render_roles_page')
        );
        
        add_submenu_page(
            'wp-roles-permissions',
            __('Assign User Roles', 'wp-roles-permissions'),
            __('Assign User Roles', 'wp-roles-permissions'),
            'manage_options',
            'wp-roles-permissions-users',
            array($this, 'render_users_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'wp-roles-permissions') === false) {
            return;
        }
        
        // Enqueue admin styles
        wp_enqueue_style(
            'wp-roles-permissions-admin',
            WP_ROLES_PERMISSIONS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WP_ROLES_PERMISSIONS_VERSION
        );
    }
    
    /**
     * Handle role actions (create, edit, delete)
     */
    public function handle_role_actions() {
        // Check if we're on the roles page
        if (!isset($_GET['page']) || $_GET['page'] !== 'wp-roles-permissions') {
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $role_manager = new WP_Roles_Permissions_Role_Manager();
        
        // Handle role creation
        if (isset($_POST['create_role']) && isset($_POST['role_slug']) && isset($_POST['role_name'])) {
            check_admin_referer('create_role_action', 'create_role_nonce');
            
            $role_slug = sanitize_key($_POST['role_slug']);
            $role_name = sanitize_text_field($_POST['role_name']);
            $capabilities = isset($_POST['capabilities']) ? array_map('sanitize_text_field', $_POST['capabilities']) : array();
            
            // Convert capabilities array to associative array
            $caps = array();
            foreach ($capabilities as $cap) {
                $caps[$cap] = true;
            }
            
            $result = $role_manager->create_role($role_slug, $role_name, $caps);
            
            if (is_wp_error($result)) {
                add_settings_error('wp_roles_permissions', 'role_error', $result->get_error_message(), 'error');
            } else {
                add_settings_error('wp_roles_permissions', 'role_created', __('Role created successfully!', 'wp-roles-permissions'), 'success');
            }
        }
        
        // Handle role update
        if (isset($_POST['update_role']) && isset($_POST['role_slug']) && isset($_POST['role_name'])) {
            check_admin_referer('update_role_action', 'update_role_nonce');
            
            $role_slug = sanitize_key($_POST['role_slug']);
            $role_name = sanitize_text_field($_POST['role_name']);
            $capabilities = isset($_POST['capabilities']) ? array_map('sanitize_text_field', $_POST['capabilities']) : array();
            
            // Convert capabilities array to associative array
            $caps = array();
            foreach ($capabilities as $cap) {
                $caps[$cap] = true;
            }
            
            $result = $role_manager->update_role($role_slug, $role_name, $caps);
            
            if (is_wp_error($result)) {
                add_settings_error('wp_roles_permissions', 'role_error', $result->get_error_message(), 'error');
            } else {
                add_settings_error('wp_roles_permissions', 'role_updated', __('Role updated successfully!', 'wp-roles-permissions'), 'success');
            }
        }
        
        // Handle role deletion
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['role'])) {
            check_admin_referer('delete_role_' . $_GET['role']);
            
            $role_slug = sanitize_key($_GET['role']);
            $result = $role_manager->delete_role($role_slug);
            
            if (is_wp_error($result)) {
                add_settings_error('wp_roles_permissions', 'role_error', $result->get_error_message(), 'error');
            } else {
                add_settings_error('wp_roles_permissions', 'role_deleted', __('Role deleted successfully!', 'wp-roles-permissions'), 'success');
            }
            
            // Redirect to remove query args
            wp_redirect(admin_url('admin.php?page=wp-roles-permissions'));
            exit;
        }
    }
    
    /**
     * Handle user role assignment actions
     */
    public function handle_user_role_actions() {
        // Check if we're on the users page
        if (!isset($_GET['page']) || $_GET['page'] !== 'wp-roles-permissions-users') {
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $role_manager = new WP_Roles_Permissions_Role_Manager();
        
        // Handle role assignment
        if (isset($_POST['assign_role']) && isset($_POST['user_id']) && isset($_POST['role'])) {
            check_admin_referer('assign_role_action', 'assign_role_nonce');
            
            $user_id = intval($_POST['user_id']);
            $role_slug = sanitize_key($_POST['role']);
            $replace = isset($_POST['replace_roles']) ? true : false;
            
            $result = $role_manager->assign_role_to_user($user_id, $role_slug, $replace);
            
            if (is_wp_error($result)) {
                add_settings_error('wp_roles_permissions', 'role_error', $result->get_error_message(), 'error');
            } else {
                add_settings_error('wp_roles_permissions', 'role_assigned', __('Role assigned successfully!', 'wp-roles-permissions'), 'success');
            }
        }
        
        // Handle role removal
        if (isset($_POST['remove_role']) && isset($_POST['user_id']) && isset($_POST['role'])) {
            check_admin_referer('remove_role_action', 'remove_role_nonce');
            
            $user_id = intval($_POST['user_id']);
            $role_slug = sanitize_key($_POST['role']);
            
            $result = $role_manager->remove_role_from_user($user_id, $role_slug);
            
            if (is_wp_error($result)) {
                add_settings_error('wp_roles_permissions', 'role_error', $result->get_error_message(), 'error');
            } else {
                add_settings_error('wp_roles_permissions', 'role_removed', __('Role removed successfully!', 'wp-roles-permissions'), 'success');
            }
        }
    }
    
    /**
     * Render the roles management page
     */
    public function render_roles_page() {
        $role_manager = new WP_Roles_Permissions_Role_Manager();
        $custom_roles = $role_manager->get_custom_roles();
        $all_capabilities = $role_manager->get_all_capabilities();
        
        // Get role to edit if specified
        $edit_role = null;
        if (isset($_GET['edit']) && !empty($_GET['edit'])) {
            $edit_role_slug = sanitize_key($_GET['edit']);
            $edit_role = get_role($edit_role_slug);
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Roles & Permissions Management', 'wp-roles-permissions'); ?></h1>
            
            <?php settings_errors('wp_roles_permissions'); ?>
            
            <div class="wp-roles-permissions-admin">
                <div class="wp-roles-permissions-form-container">
                    <h2><?php echo $edit_role ? __('Edit Role', 'wp-roles-permissions') : __('Create New Role', 'wp-roles-permissions'); ?></h2>
                    
                    <form method="post" action="">
                        <?php
                        if ($edit_role) {
                            wp_nonce_field('update_role_action', 'update_role_nonce');
                            echo '<input type="hidden" name="role_slug" value="' . esc_attr($edit_role_slug) . '">';
                        } else {
                            wp_nonce_field('create_role_action', 'create_role_nonce');
                        }
                        ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="role_slug"><?php _e('Role Slug', 'wp-roles-permissions'); ?></label>
                                </th>
                                <td>
                                    <?php if ($edit_role): ?>
                                        <input type="text" id="role_slug" value="<?php echo esc_attr($edit_role_slug); ?>" disabled class="regular-text">
                                        <p class="description"><?php _e('Role slug cannot be changed after creation.', 'wp-roles-permissions'); ?></p>
                                    <?php else: ?>
                                        <input type="text" name="role_slug" id="role_slug" class="regular-text" required>
                                        <p class="description"><?php _e('Lowercase letters, numbers, and underscores only.', 'wp-roles-permissions'); ?></p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="role_name"><?php _e('Role Name', 'wp-roles-permissions'); ?></label>
                                </th>
                                <td>
                                    <?php
                                    $role_name = '';
                                    if ($edit_role && isset($custom_roles[$edit_role_slug])) {
                                        $role_name = $custom_roles[$edit_role_slug]['name'];
                                    }
                                    ?>
                                    <input type="text" name="role_name" id="role_name" value="<?php echo esc_attr($role_name); ?>" class="regular-text" required>
                                    <p class="description"><?php _e('Display name for the role.', 'wp-roles-permissions'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php _e('Capabilities', 'wp-roles-permissions'); ?>
                                </th>
                                <td>
                                    <div class="capabilities-list">
                                        <?php
                                        sort($all_capabilities);
                                        $current_capabilities = $edit_role ? array_keys($edit_role->capabilities) : array();
                                        
                                        foreach ($all_capabilities as $cap):
                                            $checked = in_array($cap, $current_capabilities) ? 'checked' : '';
                                        ?>
                                            <label class="capability-checkbox-label">
                                                <input type="checkbox" name="capabilities[]" value="<?php echo esc_attr($cap); ?>" <?php echo $checked; ?>>
                                                <?php echo esc_html($cap); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        
                        <?php if ($edit_role): ?>
                            <p class="submit">
                                <input type="submit" name="update_role" class="button button-primary" value="<?php _e('Update Role', 'wp-roles-permissions'); ?>">
                                <a href="<?php echo admin_url('admin.php?page=wp-roles-permissions'); ?>" class="button"><?php _e('Cancel', 'wp-roles-permissions'); ?></a>
                            </p>
                        <?php else: ?>
                            <p class="submit">
                                <input type="submit" name="create_role" class="button button-primary" value="<?php _e('Create Role', 'wp-roles-permissions'); ?>">
                            </p>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="wp-roles-permissions-list-container">
                    <h2><?php _e('Custom Roles', 'wp-roles-permissions'); ?></h2>
                    
                    <?php if (empty($custom_roles)): ?>
                        <p><?php _e('No custom roles created yet.', 'wp-roles-permissions'); ?></p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Role Name', 'wp-roles-permissions'); ?></th>
                                    <th><?php _e('Role Slug', 'wp-roles-permissions'); ?></th>
                                    <th><?php _e('Capabilities', 'wp-roles-permissions'); ?></th>
                                    <th><?php _e('Actions', 'wp-roles-permissions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($custom_roles as $slug => $role_data): 
                                    $role = get_role($slug);
                                    if (!$role) continue;
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html($role_data['name']); ?></strong></td>
                                    <td><?php echo esc_html($slug); ?></td>
                                    <td><?php echo esc_html(count($role->capabilities)); ?> capabilities</td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=wp-roles-permissions&edit=' . urlencode($slug)); ?>" class="button button-small">
                                            <?php _e('Edit', 'wp-roles-permissions'); ?>
                                        </a>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wp-roles-permissions&action=delete&role=' . urlencode($slug)), 'delete_role_' . $slug); ?>" 
                                           class="button button-small" 
                                           onclick="return confirm('<?php _e('Are you sure you want to delete this role?', 'wp-roles-permissions'); ?>');">
                                            <?php _e('Delete', 'wp-roles-permissions'); ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render the user role assignment page
     */
    public function render_users_page() {
        global $wp_roles;
        
        // Get all users
        $users = get_users(array('orderby' => 'display_name'));
        
        // Get user to edit if specified
        $edit_user = null;
        if (isset($_GET['user']) && !empty($_GET['user'])) {
            $edit_user_id = intval($_GET['user']);
            $edit_user = get_userdata($edit_user_id);
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Assign User Roles', 'wp-roles-permissions'); ?></h1>
            
            <?php settings_errors('wp_roles_permissions'); ?>
            
            <div class="wp-roles-permissions-users-admin">
                <?php if ($edit_user): ?>
                    <div class="wp-roles-permissions-user-edit">
                        <h2><?php printf(__('Manage Roles for %s', 'wp-roles-permissions'), $edit_user->display_name); ?></h2>
                        
                        <div class="user-roles-section">
                            <h3><?php _e('Current Roles', 'wp-roles-permissions'); ?></h3>
                            <?php if (empty($edit_user->roles)): ?>
                                <p><?php _e('No roles assigned.', 'wp-roles-permissions'); ?></p>
                            <?php else: ?>
                                <ul class="current-roles-list">
                                    <?php foreach ($edit_user->roles as $role_slug): 
                                        $role_name = isset($wp_roles->role_names[$role_slug]) ? $wp_roles->role_names[$role_slug] : $role_slug;
                                    ?>
                                        <li>
                                            <?php echo esc_html($role_name); ?>
                                            <form method="post" class="inline-form">
                                                <?php wp_nonce_field('remove_role_action', 'remove_role_nonce'); ?>
                                                <input type="hidden" name="user_id" value="<?php echo $edit_user->ID; ?>">
                                                <input type="hidden" name="role" value="<?php echo esc_attr($role_slug); ?>">
                                                <button type="submit" name="remove_role" class="button button-small">
                                                    <?php _e('Remove', 'wp-roles-permissions'); ?>
                                                </button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <div class="add-role-section">
                            <h3><?php _e('Add Role', 'wp-roles-permissions'); ?></h3>
                            <form method="post">
                                <?php wp_nonce_field('assign_role_action', 'assign_role_nonce'); ?>
                                <input type="hidden" name="user_id" value="<?php echo $edit_user->ID; ?>">
                                
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="role"><?php _e('Select Role', 'wp-roles-permissions'); ?></label>
                                        </th>
                                        <td>
                                            <select name="role" id="role" required>
                                                <option value=""><?php _e('-- Select Role --', 'wp-roles-permissions'); ?></option>
                                                <?php foreach ($wp_roles->role_names as $slug => $name): ?>
                                                    <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"></th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="replace_roles" value="1">
                                                <?php _e('Replace existing roles', 'wp-roles-permissions'); ?>
                                            </label>
                                            <p class="description"><?php _e('If checked, removes all existing roles and sets only this role.', 'wp-roles-permissions'); ?></p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p class="submit">
                                    <input type="submit" name="assign_role" class="button button-primary" value="<?php _e('Add Role', 'wp-roles-permissions'); ?>">
                                    <a href="<?php echo admin_url('admin.php?page=wp-roles-permissions-users'); ?>" class="button"><?php _e('Back to Users', 'wp-roles-permissions'); ?></a>
                                </p>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('User', 'wp-roles-permissions'); ?></th>
                                <th><?php _e('Email', 'wp-roles-permissions'); ?></th>
                                <th><?php _e('Current Roles', 'wp-roles-permissions'); ?></th>
                                <th><?php _e('Actions', 'wp-roles-permissions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?php echo esc_html($user->display_name); ?></strong></td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td>
                                    <?php 
                                    if (empty($user->roles)) {
                                        _e('No roles', 'wp-roles-permissions');
                                    } else {
                                        $role_names = array();
                                        foreach ($user->roles as $role_slug) {
                                            $role_names[] = isset($wp_roles->role_names[$role_slug]) ? $wp_roles->role_names[$role_slug] : $role_slug;
                                        }
                                        echo esc_html(implode(', ', $role_names));
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=wp-roles-permissions-users&user=' . $user->ID); ?>" class="button button-small">
                                        <?php _e('Manage Roles', 'wp-roles-permissions'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

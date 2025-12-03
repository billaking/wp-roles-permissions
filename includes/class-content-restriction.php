<?php
/**
 * Content Restriction Class
 * Handles content access restriction based on roles
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Roles_Permissions_Content_Restriction {
    
    /**
     * Flag to prevent multiple filter additions
     *
     * @var bool
     */
    private static $filter_added = false;
    
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
        // Add meta box for assigning roles to posts/pages
        add_action('add_meta_boxes', array($this, 'add_role_meta_box'));
        
        // Save meta box data
        add_action('save_post', array($this, 'save_role_meta_box'), 10, 2);
        
        // Filter content on frontend
        add_filter('the_content', array($this, 'restrict_content'), 10, 1);
        
        // Filter posts in queries - add once
        if (!self::$filter_added) {
            add_filter('the_posts', array($this, 'filter_posts_by_role'), 10, 2);
            self::$filter_added = true;
        }
        
        // Handle direct access to restricted posts
        add_action('template_redirect', array($this, 'check_single_post_access'));
        
        // Enqueue styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles() {
        global $post;
        
        // Only load on post/page edit screens
        $screen = get_current_screen();
        if ($screen && in_array($screen->post_type, array('post', 'page'))) {
            wp_enqueue_style(
                'wp-roles-permissions-admin',
                WP_ROLES_PERMISSIONS_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WP_ROLES_PERMISSIONS_VERSION
            );
        }
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'wp-roles-permissions-frontend',
            WP_ROLES_PERMISSIONS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WP_ROLES_PERMISSIONS_VERSION
        );
    }
    
    /**
     * Add meta box for role assignment
     */
    public function add_role_meta_box() {
        $post_types = array('post', 'page');
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'wp_roles_permissions_roles',
                __('Content Access Restrictions', 'wp-roles-permissions'),
                array($this, 'render_role_meta_box'),
                $post_type,
                'side',
                'default'
            );
        }
    }
    
    /**
     * Render the role meta box
     *
     * @param WP_Post $post Current post object
     */
    public function render_role_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('wp_roles_permissions_save_roles', 'wp_roles_permissions_nonce');
        
        // Get current assigned roles
        $assigned_roles = get_post_meta($post->ID, '_wp_roles_permissions_roles', true);
        if (!is_array($assigned_roles)) {
            $assigned_roles = array();
        }
        
        // Get all roles
        global $wp_roles;
        $all_roles = $wp_roles->get_names();
        
        echo '<div class="wp-roles-permissions-meta-box">';
        echo '<p>' . __('Select roles that can access this content. Leave unchecked for public access.', 'wp-roles-permissions') . '</p>';
        
        foreach ($all_roles as $role_slug => $role_name) {
            $checked = in_array($role_slug, $assigned_roles) ? 'checked="checked"' : '';
            echo '<label class="wp-roles-permissions-role-label">';
            echo '<input type="checkbox" name="wp_roles_permissions_roles[]" value="' . esc_attr($role_slug) . '" ' . $checked . '> ';
            echo esc_html($role_name);
            echo '</label>';
        }
        
        echo '</div>';
    }
    
    /**
     * Save meta box data
     *
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function save_role_meta_box($post_id, $post) {
        // Check nonce
        if (!isset($_POST['wp_roles_permissions_nonce']) || 
            !wp_verify_nonce($_POST['wp_roles_permissions_nonce'], 'wp_roles_permissions_save_roles')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check post type
        if (!in_array($post->post_type, array('post', 'page'))) {
            return;
        }
        
        // Check permissions
        $post_type_object = get_post_type_object($post->post_type);
        if (!current_user_can($post_type_object->cap->edit_post, $post_id)) {
            return;
        }
        
        // Save roles
        if (isset($_POST['wp_roles_permissions_roles']) && is_array($_POST['wp_roles_permissions_roles'])) {
            $roles = array_map('sanitize_text_field', $_POST['wp_roles_permissions_roles']);
            update_post_meta($post_id, '_wp_roles_permissions_roles', $roles);
        } else {
            // No roles selected, delete meta to make it public
            delete_post_meta($post_id, '_wp_roles_permissions_roles');
        }
    }
    
    /**
     * Check if current user can access post
     *
     * @param int $post_id Post ID
     * @return bool True if user can access, false otherwise
     */
    public function can_user_access_post($post_id) {
        // Get assigned roles for this post
        $assigned_roles = get_post_meta($post_id, '_wp_roles_permissions_roles', true);
        
        // If no roles assigned, content is public
        if (empty($assigned_roles) || !is_array($assigned_roles)) {
            return true;
        }
        
        // If user is not logged in, deny access
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Get current user
        $current_user = wp_get_current_user();
        
        // Administrators can always access
        if (in_array('administrator', $current_user->roles)) {
            return true;
        }
        
        // Check if user has any of the assigned roles
        $user_roles = $current_user->roles;
        $has_access = !empty(array_intersect($user_roles, $assigned_roles));
        
        return $has_access;
    }
    
    /**
     * Restrict content on frontend
     *
     * @param string $content Post content
     * @return string Modified content
     */
    public function restrict_content($content) {
        // Only apply on singular posts/pages
        if (!is_singular(array('post', 'page'))) {
            return $content;
        }
        
        global $post;
        
        // Check if user can access
        if (!$this->can_user_access_post($post->ID)) {
            $message = '<div class="wp-roles-permissions-restricted">';
            $message .= '<h3>' . __('Restricted Content', 'wp-roles-permissions') . '</h3>';
            $message .= '<p>' . __('This content is restricted. You need to have the appropriate role to view it.', 'wp-roles-permissions') . '</p>';
            
            if (!is_user_logged_in()) {
                $message .= '<p><a href="' . wp_login_url(get_permalink($post->ID)) . '">' . __('Please log in to access this content.', 'wp-roles-permissions') . '</a></p>';
            } else {
                $message .= '<p>' . __('Contact your administrator if you believe you should have access.', 'wp-roles-permissions') . '</p>';
            }
            
            $message .= '</div>';
            
            return $message;
        }
        
        return $content;
    }
    
    /**
     * Filter posts based on user role access
     *
     * @param array $posts Array of post objects
     * @param WP_Query $query Query object
     * @return array Filtered array of posts
     */
    public function filter_posts_by_role($posts, $query) {
        // Remove this filter to prevent infinite loops
        remove_filter('the_posts', array($this, 'filter_posts_by_role'), 10);
        
        // Only apply on frontend main query
        if (is_admin() || !$query->is_main_query() || $query->is_singular()) {
            return $posts;
        }
        
        // Filter posts user can't access
        $filtered_posts = array();
        foreach ($posts as $post) {
            if ($this->can_user_access_post($post->ID)) {
                $filtered_posts[] = $post;
            }
        }
        
        return $filtered_posts;
    }
    
    /**
     * Check access when viewing single post directly
     */
    public function check_single_post_access() {
        // Only check on singular posts/pages
        if (!is_singular(array('post', 'page'))) {
            return;
        }
        
        global $post;
        
        // Check if user can access
        if (!$this->can_user_access_post($post->ID)) {
            if (!is_user_logged_in()) {
                // Redirect to login
                auth_redirect();
            } else {
                // Show 403 forbidden
                wp_die(
                    __('You do not have permission to access this content.', 'wp-roles-permissions'),
                    __('Access Denied', 'wp-roles-permissions'),
                    array('response' => 403)
                );
            }
        }
    }
    
    /**
     * Get assigned roles for a post
     *
     * @param int $post_id Post ID
     * @return array Array of role slugs
     */
    public function get_post_roles($post_id) {
        $roles = get_post_meta($post_id, '_wp_roles_permissions_roles', true);
        return is_array($roles) ? $roles : array();
    }
}

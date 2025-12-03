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
        
        // Filter posts in queries
        add_action('pre_get_posts', array($this, 'filter_restricted_posts'));
        
        // Handle direct access to restricted posts
        add_action('template_redirect', array($this, 'check_single_post_access'));
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
            echo '<label style="display: block; margin-bottom: 8px;">';
            echo '<input type="checkbox" name="wp_roles_permissions_roles[]" value="' . esc_attr($role_slug) . '" ' . $checked . '> ';
            echo esc_html($role_name);
            echo '</label>';
        }
        
        echo '</div>';
        
        // Add inline styles
        echo '<style>
            .wp-roles-permissions-meta-box {
                padding: 5px 0;
            }
            .wp-roles-permissions-meta-box label {
                cursor: pointer;
            }
            .wp-roles-permissions-meta-box label:hover {
                background-color: #f0f0f0;
                padding: 2px 4px;
                margin: 0 -4px;
            }
        </style>';
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
            
            // Add inline styles
            $message .= '<style>
                .wp-roles-permissions-restricted {
                    padding: 20px;
                    background-color: #fff3cd;
                    border: 1px solid #ffc107;
                    border-radius: 4px;
                    margin: 20px 0;
                }
                .wp-roles-permissions-restricted h3 {
                    margin-top: 0;
                    color: #856404;
                }
                .wp-roles-permissions-restricted p {
                    color: #856404;
                }
                .wp-roles-permissions-restricted a {
                    color: #004085;
                    text-decoration: underline;
                }
            </style>';
            
            return $message;
        }
        
        return $content;
    }
    
    /**
     * Filter restricted posts from queries
     *
     * @param WP_Query $query Query object
     */
    public function filter_restricted_posts($query) {
        // Only apply on frontend and main query
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        // Don't filter on singular pages (handled by restrict_content)
        if ($query->is_singular()) {
            return;
        }
        
        // Add meta query to exclude restricted posts
        add_filter('posts_where', array($this, 'posts_where_filter'), 10, 2);
    }
    
    /**
     * Modify WHERE clause to filter restricted posts
     *
     * @param string $where WHERE clause
     * @param WP_Query $query Query object
     * @return string Modified WHERE clause
     */
    public function posts_where_filter($where, $query) {
        global $wpdb;
        
        // Remove this filter to prevent infinite loops
        remove_filter('posts_where', array($this, 'posts_where_filter'), 10);
        
        // Only apply on frontend main query for posts/pages
        if (is_admin() || !$query->is_main_query() || $query->is_singular()) {
            return $where;
        }
        
        // If user is not logged in, exclude posts with role restrictions
        if (!is_user_logged_in()) {
            $where .= " AND {$wpdb->posts}.ID NOT IN (
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE meta_key = '_wp_roles_permissions_roles'
            )";
        } else {
            // Get current user roles
            $current_user = wp_get_current_user();
            
            // Administrators can see everything
            if (!in_array('administrator', $current_user->roles)) {
                $user_roles = $current_user->roles;
                
                if (!empty($user_roles)) {
                    // Exclude posts that have role restrictions user doesn't have
                    $roles_placeholders = implode(',', array_fill(0, count($user_roles), '%s'));
                    
                    $where .= $wpdb->prepare(
                        " AND ({$wpdb->posts}.ID NOT IN (
                            SELECT post_id FROM {$wpdb->postmeta} 
                            WHERE meta_key = '_wp_roles_permissions_roles'
                        ) OR {$wpdb->posts}.ID IN (
                            SELECT post_id FROM {$wpdb->postmeta} 
                            WHERE meta_key = '_wp_roles_permissions_roles'
                            AND meta_value LIKE %s",
                        '%' . $wpdb->esc_like(serialize($user_roles[0])) . '%'
                    );
                    
                    for ($i = 1; $i < count($user_roles); $i++) {
                        $where .= $wpdb->prepare(
                            " OR meta_value LIKE %s",
                            '%' . $wpdb->esc_like(serialize($user_roles[$i])) . '%'
                        );
                    }
                    
                    $where .= "))";
                }
            }
        }
        
        return $where;
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
            // Get assigned roles
            $assigned_roles = get_post_meta($post->ID, '_wp_roles_permissions_roles', true);
            
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

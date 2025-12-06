<?php
/**
 * Template Manager Class
 * Handles custom post templates with role-based editing permissions
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Roles_Permissions_Template_Manager {
    
    /**
     * Template option key
     */
    const TEMPLATE_OPTION_KEY = 'wp_roles_permissions_templates';
    
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
        // Register default templates on first run
        $this->register_default_templates();
        
        // Override single post template
        add_filter('single_template', array($this, 'load_custom_template'), 99);
        
        // Add template selector to post editor
        add_action('add_meta_boxes', array($this, 'add_template_selector_meta_box'));
        add_action('save_post', array($this, 'save_template_selection'), 10, 2);
        
        // Register shortcodes for templates
        $this->register_template_shortcodes();
    }
    
    /**
     * Register default templates
     */
    private function register_default_templates() {
        $templates = get_option(self::TEMPLATE_OPTION_KEY, array());
        
        // Check if Default Group Posts template exists
        if (!isset($templates['default_group_posts'])) {
            $default_template = $this->get_default_group_posts_template();
            $templates['default_group_posts'] = $default_template;
            update_option(self::TEMPLATE_OPTION_KEY, $templates);
        }
    }
    
    /**
     * Get default template content
     */
    private function get_default_group_posts_template() {
        return array(
            'name' => 'Default Group Posts',
            'slug' => 'default_group_posts',
            'content' => '<article id="post-[post_id]" class="[post_class]">
    <header class="entry-header">
        <h1 class="entry-title">[post_title]</h1>
        <div class="entry-meta">
            <span class="posted-on">Posted on [post_date]</span>
            <span class="byline">by [post_author]</span>
            [post_categories]
        </div>
    </header>
    
    <div class="entry-content">
        [post_content]
    </div>
    
    <footer class="entry-footer">
        [post_tags]
        [post_edit_link]
    </footer>
</article>

[post_comments]',
            'description' => 'Default template for group posts with customizable shortcodes',
            'created' => current_time('mysql'),
            'updated' => current_time('mysql'),
            'edit_capability' => 'manage_options'
        );
    }
    
    /**
     * Get all templates
     */
    public function get_templates() {
        return get_option(self::TEMPLATE_OPTION_KEY, array());
    }
    
    /**
     * Get a specific template
     */
    public function get_template($slug) {
        $templates = $this->get_templates();
        return isset($templates[$slug]) ? $templates[$slug] : null;
    }
    
    /**
     * Update a template
     */
    public function update_template($slug, $data) {
        $templates = $this->get_templates();
        
        if (!isset($templates[$slug])) {
            return new WP_Error('template_not_found', __('Template not found.', 'wp-roles-permissions'));
        }
        
        // Preserve certain fields
        $templates[$slug]['name'] = isset($data['name']) ? sanitize_text_field($data['name']) : $templates[$slug]['name'];
        $templates[$slug]['content'] = isset($data['content']) ? wp_kses_post($data['content']) : $templates[$slug]['content'];
        $templates[$slug]['description'] = isset($data['description']) ? sanitize_text_field($data['description']) : $templates[$slug]['description'];
        $templates[$slug]['edit_capability'] = isset($data['edit_capability']) ? sanitize_text_field($data['edit_capability']) : $templates[$slug]['edit_capability'];
        $templates[$slug]['updated'] = current_time('mysql');
        
        update_option(self::TEMPLATE_OPTION_KEY, $templates);
        
        return true;
    }
    
    /**
     * Create a new template
     */
    public function create_template($slug, $data) {
        $templates = $this->get_templates();
        
        if (isset($templates[$slug])) {
            return new WP_Error('template_exists', __('Template already exists.', 'wp-roles-permissions'));
        }
        
        $templates[$slug] = array(
            'name' => sanitize_text_field($data['name']),
            'slug' => sanitize_key($slug),
            'content' => wp_kses_post($data['content']),
            'description' => isset($data['description']) ? sanitize_text_field($data['description']) : '',
            'created' => current_time('mysql'),
            'updated' => current_time('mysql'),
            'edit_capability' => isset($data['edit_capability']) ? sanitize_text_field($data['edit_capability']) : 'manage_options'
        );
        
        update_option(self::TEMPLATE_OPTION_KEY, $templates);
        
        return true;
    }
    
    /**
     * Delete a template
     */
    public function delete_template($slug) {
        // Prevent deletion of default template
        if ($slug === 'default_group_posts') {
            return new WP_Error('cannot_delete', __('Cannot delete the default template.', 'wp-roles-permissions'));
        }
        
        $templates = $this->get_templates();
        
        if (!isset($templates[$slug])) {
            return new WP_Error('template_not_found', __('Template not found.', 'wp-roles-permissions'));
        }
        
        unset($templates[$slug]);
        update_option(self::TEMPLATE_OPTION_KEY, $templates);
        
        return true;
    }
    
    /**
     * Check if user can edit template
     */
    public function user_can_edit_template($slug) {
        $template = $this->get_template($slug);
        
        if (!$template) {
            return false;
        }
        
        $capability = isset($template['edit_capability']) ? $template['edit_capability'] : 'manage_options';
        
        return current_user_can($capability);
    }
    
    /**
     * Add template selector meta box
     */
    public function add_template_selector_meta_box() {
        add_meta_box(
            'wp_roles_permissions_template',
            __('Post Template', 'wp-roles-permissions'),
            array($this, 'render_template_selector_meta_box'),
            'post',
            'side',
            'default'
        );
    }
    
    /**
     * Render template selector meta box
     */
    public function render_template_selector_meta_box($post) {
        wp_nonce_field('save_template_selection', 'template_selection_nonce');
        
        $current_template = get_post_meta($post->ID, '_wp_roles_permissions_template', true);
        $templates = $this->get_templates();
        
        ?>
        <p>
            <label for="wp_roles_permissions_template"><?php _e('Select Template:', 'wp-roles-permissions'); ?></label>
            <select name="wp_roles_permissions_template" id="wp_roles_permissions_template" style="width: 100%;">
                <option value=""><?php _e('WordPress Default', 'wp-roles-permissions'); ?></option>
                <?php foreach ($templates as $slug => $template): ?>
                    <option value="<?php echo esc_attr($slug); ?>" <?php selected($current_template, $slug); ?>>
                        <?php echo esc_html($template['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p class="description">
            <?php _e('Select a custom template for this post or use the WordPress default.', 'wp-roles-permissions'); ?>
        </p>
        <?php
    }
    
    /**
     * Save template selection
     */
    public function save_template_selection($post_id, $post) {
        // Check nonce
        if (!isset($_POST['template_selection_nonce']) || !wp_verify_nonce($_POST['template_selection_nonce'], 'save_template_selection')) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Don't save on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save template selection
        if (isset($_POST['wp_roles_permissions_template'])) {
            $template = sanitize_text_field($_POST['wp_roles_permissions_template']);
            update_post_meta($post_id, '_wp_roles_permissions_template', $template);
        } else {
            delete_post_meta($post_id, '_wp_roles_permissions_template');
        }
    }
    
    /**
     * Load custom template
     */
    public function load_custom_template($template) {
        global $post;
        
        if (!is_single() || !$post) {
            return $template;
        }
        
        $custom_template = get_post_meta($post->ID, '_wp_roles_permissions_template', true);
        
        if (empty($custom_template)) {
            return $template;
        }
        
        $template_data = $this->get_template($custom_template);
        
        if (!$template_data) {
            return $template;
        }
        
        // Create a temporary template file
        $temp_template = WP_ROLES_PERMISSIONS_PLUGIN_DIR . 'templates/temp-single.php';
        
        // Ensure templates directory exists
        if (!file_exists(WP_ROLES_PERMISSIONS_PLUGIN_DIR . 'templates')) {
            mkdir(WP_ROLES_PERMISSIONS_PLUGIN_DIR . 'templates', 0755, true);
        }
        
        // Generate the template file
        $this->generate_template_file($temp_template, $template_data);
        
        return $temp_template;
    }
    
    /**
     * Generate template file
     */
    private function generate_template_file($file_path, $template_data) {
        $content = "<?php\n";
        $content .= "/**\n";
        $content .= " * Template: " . $template_data['name'] . "\n";
        $content .= " * Generated by WP Roles & Permissions\n";
        $content .= " */\n\n";
        $content .= "get_header();\n\n";
        $content .= "while (have_posts()) : the_post();\n";
        $content .= "    // Template content\n";
        $content .= "    echo do_shortcode('" . addslashes($template_data['content']) . "');\n";
        $content .= "endwhile;\n\n";
        $content .= "get_footer();\n";
        
        file_put_contents($file_path, $content);
    }
    
    /**
     * Register template shortcodes
     */
    private function register_template_shortcodes() {
        add_shortcode('post_id', array($this, 'shortcode_post_id'));
        add_shortcode('post_class', array($this, 'shortcode_post_class'));
        add_shortcode('post_title', array($this, 'shortcode_post_title'));
        add_shortcode('post_date', array($this, 'shortcode_post_date'));
        add_shortcode('post_author', array($this, 'shortcode_post_author'));
        add_shortcode('post_content', array($this, 'shortcode_post_content'));
        add_shortcode('post_excerpt', array($this, 'shortcode_post_excerpt'));
        add_shortcode('post_categories', array($this, 'shortcode_post_categories'));
        add_shortcode('post_tags', array($this, 'shortcode_post_tags'));
        add_shortcode('post_edit_link', array($this, 'shortcode_post_edit_link'));
        add_shortcode('post_comments', array($this, 'shortcode_post_comments'));
        add_shortcode('post_thumbnail', array($this, 'shortcode_post_thumbnail'));
    }
    
    /**
     * Shortcode: Post ID
     */
    public function shortcode_post_id($atts) {
        return get_the_ID();
    }
    
    /**
     * Shortcode: Post Class
     */
    public function shortcode_post_class($atts) {
        return implode(' ', get_post_class());
    }
    
    /**
     * Shortcode: Post Title
     */
    public function shortcode_post_title($atts) {
        return get_the_title();
    }
    
    /**
     * Shortcode: Post Date
     */
    public function shortcode_post_date($atts) {
        $atts = shortcode_atts(array(
            'format' => get_option('date_format')
        ), $atts);
        
        return get_the_date($atts['format']);
    }
    
    /**
     * Shortcode: Post Author
     */
    public function shortcode_post_author($atts) {
        $atts = shortcode_atts(array(
            'link' => 'yes'
        ), $atts);
        
        if ($atts['link'] === 'yes') {
            return '<a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . get_the_author() . '</a>';
        }
        
        return get_the_author();
    }
    
    /**
     * Shortcode: Post Content
     */
    public function shortcode_post_content($atts) {
        global $post;
        
        // Remove shortcode filter temporarily to prevent infinite loops
        remove_filter('the_content', array($this, 'shortcode_post_content'));
        
        $content = apply_filters('the_content', get_the_content());
        
        // Re-add the filter
        add_filter('the_content', array($this, 'shortcode_post_content'));
        
        return $content;
    }
    
    /**
     * Shortcode: Post Excerpt
     */
    public function shortcode_post_excerpt($atts) {
        return get_the_excerpt();
    }
    
    /**
     * Shortcode: Post Categories
     */
    public function shortcode_post_categories($atts) {
        $atts = shortcode_atts(array(
            'separator' => ', ',
            'label' => 'Categories: '
        ), $atts);
        
        $categories = get_the_category_list($atts['separator']);
        
        if ($categories) {
            return '<span class="cat-links">' . $atts['label'] . $categories . '</span>';
        }
        
        return '';
    }
    
    /**
     * Shortcode: Post Tags
     */
    public function shortcode_post_tags($atts) {
        $atts = shortcode_atts(array(
            'separator' => ', ',
            'label' => 'Tags: '
        ), $atts);
        
        $tags = get_the_tag_list($atts['label'], $atts['separator']);
        
        if ($tags) {
            return '<span class="tags-links">' . $tags . '</span>';
        }
        
        return '';
    }
    
    /**
     * Shortcode: Post Edit Link
     */
    public function shortcode_post_edit_link($atts) {
        $atts = shortcode_atts(array(
            'text' => 'Edit',
            'before' => '<span class="edit-link">',
            'after' => '</span>'
        ), $atts);
        
        ob_start();
        edit_post_link($atts['text'], $atts['before'], $atts['after']);
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Post Comments
     */
    public function shortcode_post_comments($atts) {
        if (!comments_open() && get_comments_number() == 0) {
            return '';
        }
        
        ob_start();
        comments_template();
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Post Thumbnail
     */
    public function shortcode_post_thumbnail($atts) {
        $atts = shortcode_atts(array(
            'size' => 'post-thumbnail',
            'class' => ''
        ), $atts);
        
        if (!has_post_thumbnail()) {
            return '';
        }
        
        return get_the_post_thumbnail(null, $atts['size'], array('class' => $atts['class']));
    }
}

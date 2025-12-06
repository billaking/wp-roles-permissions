<?php
/**
 * Group Shortcodes Class
 * Handles frontend display of group information via shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Roles_Permissions_Group_Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }
    
    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('wrp_group_list', array($this, 'shortcode_group_list'));
        add_shortcode('wrp_group_events', array($this, 'shortcode_group_events'));
        add_shortcode('wrp_group_members', array($this, 'shortcode_group_members'));
        add_shortcode('wrp_group_documents', array($this, 'shortcode_group_documents'));
        add_shortcode('wrp_upcoming_events', array($this, 'shortcode_upcoming_events'));
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'wp-roles-permissions-groups',
            WP_ROLES_PERMISSIONS_PLUGIN_URL . 'assets/css/groups-frontend.css',
            array(),
            WP_ROLES_PERMISSIONS_VERSION
        );
    }
    
    /**
     * Shortcode: Group List
     * Usage: [wrp_group_list category="youth" limit="10"]
     */
    public function shortcode_group_list($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => -1,
            'show_description' => 'yes',
            'show_image' => 'yes',
        ), $atts);
        
        $args = array(
            'post_type' => 'wrp_group',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => 'title',
            'order' => 'ASC',
        );
        
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'wrp_group_category',
                    'field' => 'slug',
                    'terms' => $atts['category'],
                ),
            );
        }
        
        $groups = new WP_Query($args);
        
        if (!$groups->have_posts()) {
            return '<p class="wrp-no-groups">' . __('No groups found.', 'wp-roles-permissions') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="wrp-groups-list">
            <?php while ($groups->have_posts()): $groups->the_post(); 
                $group_id = get_the_ID();
                $members = get_post_meta($group_id, '_wrp_group_members', true);
                $member_count = is_array($members) ? count($members) : 0;
            ?>
                <div class="wrp-group-item">
                    <?php if ($atts['show_image'] === 'yes' && has_post_thumbnail()): ?>
                        <div class="wrp-group-image">
                            <?php the_post_thumbnail('medium'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="wrp-group-content">
                        <h3 class="wrp-group-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <div class="wrp-group-meta">
                            <span class="wrp-member-count">
                                <?php printf(_n('%d member', '%d members', $member_count, 'wp-roles-permissions'), $member_count); ?>
                            </span>
                        </div>
                        
                        <?php if ($atts['show_description'] === 'yes'): ?>
                            <div class="wrp-group-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?php the_permalink(); ?>" class="wrp-group-link">
                            <?php _e('Learn More', 'wp-roles-permissions'); ?> →
                        </a>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Group Events
     * Usage: [wrp_group_events group_id="123" limit="5"]
     */
    public function shortcode_group_events($atts) {
        $atts = shortcode_atts(array(
            'group_id' => get_the_ID(),
            'limit' => 5,
            'show_past' => 'no',
        ), $atts);
        
        $args = array(
            'post_type' => 'wrp_event',
            'posts_per_page' => intval($atts['limit']),
            'meta_key' => '_wrp_event_group',
            'meta_value' => intval($atts['group_id']),
            'orderby' => 'meta_value',
            'order' => 'ASC',
        );
        
        if ($atts['show_past'] === 'no') {
            $args['meta_query'] = array(
                array(
                    'key' => '_wrp_event_date',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
            );
        }
        
        $events = new WP_Query($args);
        
        if (!$events->have_posts()) {
            return '<p class="wrp-no-events">' . __('No upcoming events.', 'wp-roles-permissions') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="wrp-events-list">
            <?php while ($events->have_posts()): $events->the_post(); 
                $event_id = get_the_ID();
                $event_date = get_post_meta($event_id, '_wrp_event_date', true);
                $event_time = get_post_meta($event_id, '_wrp_event_time', true);
                $event_location = get_post_meta($event_id, '_wrp_event_location', true);
            ?>
                <div class="wrp-event-item">
                    <div class="wrp-event-date-badge">
                        <span class="month"><?php echo date('M', strtotime($event_date)); ?></span>
                        <span class="day"><?php echo date('j', strtotime($event_date)); ?></span>
                    </div>
                    
                    <div class="wrp-event-details">
                        <h4 class="wrp-event-title"><?php the_title(); ?></h4>
                        
                        <div class="wrp-event-meta">
                            <?php if ($event_time): ?>
                                <span class="wrp-event-time">
                                    🕐 <?php echo esc_html($event_time); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($event_location): ?>
                                <span class="wrp-event-location">
                                    📍 <?php echo esc_html($event_location); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (has_excerpt()): ?>
                            <div class="wrp-event-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Group Members
     * Usage: [wrp_group_members group_id="123" show_contact="no"]
     */
    public function shortcode_group_members($atts) {
        $atts = shortcode_atts(array(
            'group_id' => get_the_ID(),
            'show_contact' => 'no',
            'show_role' => 'yes',
        ), $atts);
        
        $group_id = intval($atts['group_id']);
        $members = get_post_meta($group_id, '_wrp_group_members', true);
        $member_info = get_post_meta($group_id, '_wrp_member_info', true);
        
        if (!is_array($members) || empty($members)) {
            return '<p class="wrp-no-members">' . __('No members to display.', 'wp-roles-permissions') . '</p>';
        }
        
        // Check if current user is a member for contact info
        $current_user_id = get_current_user_id();
        $is_member = in_array($current_user_id, $members);
        $show_contact = ($atts['show_contact'] === 'yes' && $is_member);
        
        ob_start();
        ?>
        <div class="wrp-members-grid">
            <?php foreach ($members as $member_id): 
                $user = get_userdata($member_id);
                if (!$user) continue;
                
                $info = isset($member_info[$member_id]) ? $member_info[$member_id] : array();
                $role = isset($info['role']) ? $info['role'] : '';
            ?>
                <div class="wrp-member-card">
                    <div class="wrp-member-avatar">
                        <?php echo get_avatar($member_id, 80); ?>
                    </div>
                    
                    <div class="wrp-member-info">
                        <h4 class="wrp-member-name"><?php echo esc_html($user->display_name); ?></h4>
                        
                        <?php if ($atts['show_role'] === 'yes' && !empty($role)): ?>
                            <div class="wrp-member-role"><?php echo esc_html($role); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($show_contact): ?>
                            <div class="wrp-member-contact">
                                <?php if (!empty($user->user_email)): ?>
                                    <a href="mailto:<?php echo esc_attr($user->user_email); ?>">
                                        ✉ <?php echo esc_html($user->user_email); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($info['phone'])): ?>
                                    <br>📞 <?php echo esc_html($info['phone']); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Group Documents
     * Usage: [wrp_group_documents group_id="123" type="shared"]
     */
    public function shortcode_group_documents($atts) {
        $atts = shortcode_atts(array(
            'group_id' => get_the_ID(),
            'type' => 'shared', // 'shared' or 'private'
        ), $atts);
        
        $group_id = intval($atts['group_id']);
        
        // Check permissions for private documents
        if ($atts['type'] === 'private') {
            $post = get_post($group_id);
            if (!current_user_can('edit_post', $group_id) && get_current_user_id() != $post->post_author) {
                return '<p>' . __('You do not have permission to view these documents.', 'wp-roles-permissions') . '</p>';
            }
        }
        
        $meta_key = $atts['type'] === 'shared' ? '_wrp_shared_documents' : '_wrp_private_documents';
        $documents = get_post_meta($group_id, $meta_key, true);
        
        if (!is_array($documents) || empty($documents)) {
            return '<p class="wrp-no-documents">' . __('No documents available.', 'wp-roles-permissions') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="wrp-documents-list">
            <?php foreach ($documents as $doc): ?>
                <div class="wrp-document-item">
                    <span class="wrp-doc-icon">📄</span>
                    <a href="<?php echo esc_url($doc['url']); ?>" target="_blank" class="wrp-doc-link">
                        <?php echo esc_html($doc['title']); ?>
                    </a>
                    <span class="wrp-doc-meta">
                        <?php echo esc_html($doc['type']); ?> • 
                        <?php echo date('M j, Y', $doc['uploaded']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode: Upcoming Events (all groups)
     * Usage: [wrp_upcoming_events limit="5" days="30"]
     */
    public function shortcode_upcoming_events($atts) {
        $atts = shortcode_atts(array(
            'limit' => 5,
            'days' => 30,
        ), $atts);
        
        $end_date = date('Y-m-d', strtotime('+' . intval($atts['days']) . ' days'));
        
        $args = array(
            'post_type' => 'wrp_event',
            'posts_per_page' => intval($atts['limit']),
            'meta_query' => array(
                array(
                    'key' => '_wrp_event_date',
                    'value' => array(date('Y-m-d'), $end_date),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ),
            ),
            'meta_key' => '_wrp_event_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
        );
        
        $events = new WP_Query($args);
        
        if (!$events->have_posts()) {
            return '<p class="wrp-no-events">' . __('No upcoming events.', 'wp-roles-permissions') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="wrp-upcoming-events">
            <?php while ($events->have_posts()): $events->the_post(); 
                $event_id = get_the_ID();
                $event_date = get_post_meta($event_id, '_wrp_event_date', true);
                $event_time = get_post_meta($event_id, '_wrp_event_time', true);
                $group_id = get_post_meta($event_id, '_wrp_event_group', true);
                $group = get_post($group_id);
            ?>
                <div class="wrp-upcoming-event">
                    <div class="wrp-event-date">
                        <?php echo date('M j, Y', strtotime($event_date)); ?>
                        <?php if ($event_time): ?>
                            <br><small><?php echo esc_html($event_time); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="wrp-event-info">
                        <h4><?php the_title(); ?></h4>
                        <?php if ($group): ?>
                            <small class="wrp-event-group">
                                <?php echo esc_html($group->post_title); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

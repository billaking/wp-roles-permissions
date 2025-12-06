<?php
/**
 * Group Manager Class
 * Handles group creation and management with various capabilities
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Roles_Permissions_Group_Manager {
    
    /**
     * Custom post type for groups
     */
    const POST_TYPE = 'wrp_group';
    
    /**
     * Custom post type for events
     */
    const EVENT_POST_TYPE = 'wrp_event';
    
    /**
     * Custom post type for ministry pages
     */
    const MINISTRY_POST_TYPE = 'wrp_ministry_page';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('init', array($this, 'register_group_capabilities'));
        
        // Meta boxes
        add_action('add_meta_boxes', array($this, 'add_group_meta_boxes'));
        add_action('save_post', array($this, 'save_group_meta_boxes'), 10, 2);
        
        // AJAX handlers
        add_action('wp_ajax_wrp_add_group_member', array($this, 'ajax_add_group_member'));
        add_action('wp_ajax_wrp_remove_group_member', array($this, 'ajax_remove_group_member'));
        add_action('wp_ajax_wrp_track_attendance', array($this, 'ajax_track_attendance'));
        add_action('wp_ajax_wrp_record_donation', array($this, 'ajax_record_donation'));
    }
    
    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Register Groups post type
        register_post_type(self::POST_TYPE, array(
            'labels' => array(
                'name' => __('Groups', 'wp-roles-permissions'),
                'singular_name' => __('Group', 'wp-roles-permissions'),
                'add_new' => __('Add New Group', 'wp-roles-permissions'),
                'add_new_item' => __('Add New Group', 'wp-roles-permissions'),
                'edit_item' => __('Edit Group', 'wp-roles-permissions'),
                'new_item' => __('New Group', 'wp-roles-permissions'),
                'view_item' => __('View Group', 'wp-roles-permissions'),
                'search_items' => __('Search Groups', 'wp-roles-permissions'),
                'not_found' => __('No groups found', 'wp-roles-permissions'),
            ),
            'public' => true,
            'has_archive' => true,
            'show_in_menu' => 'wp-roles-permissions',
            'supports' => array('title', 'editor', 'thumbnail', 'author'),
            'capability_type' => 'wrp_group',
            'map_meta_cap' => true,
            'rewrite' => array('slug' => 'groups'),
        ));
        
        // Register Events post type
        register_post_type(self::EVENT_POST_TYPE, array(
            'labels' => array(
                'name' => __('Events', 'wp-roles-permissions'),
                'singular_name' => __('Event', 'wp-roles-permissions'),
                'add_new' => __('Add New Event', 'wp-roles-permissions'),
                'add_new_item' => __('Add New Event', 'wp-roles-permissions'),
                'edit_item' => __('Edit Event', 'wp-roles-permissions'),
            ),
            'public' => true,
            'show_in_menu' => false,
            'supports' => array('title', 'editor', 'thumbnail'),
            'capability_type' => 'wrp_event',
            'map_meta_cap' => true,
        ));
        
        // Register Ministry Pages post type
        register_post_type(self::MINISTRY_POST_TYPE, array(
            'labels' => array(
                'name' => __('Ministry Pages', 'wp-roles-permissions'),
                'singular_name' => __('Ministry Page', 'wp-roles-permissions'),
                'add_new' => __('Add New Page', 'wp-roles-permissions'),
                'add_new_item' => __('Add New Ministry Page', 'wp-roles-permissions'),
                'edit_item' => __('Edit Ministry Page', 'wp-roles-permissions'),
            ),
            'public' => true,
            'show_in_menu' => false,
            'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'),
            'capability_type' => 'wrp_ministry_page',
            'map_meta_cap' => true,
            'rewrite' => array('slug' => 'ministry'),
        ));
    }
    
    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Group categories
        register_taxonomy('wrp_group_category', self::POST_TYPE, array(
            'labels' => array(
                'name' => __('Group Categories', 'wp-roles-permissions'),
                'singular_name' => __('Group Category', 'wp-roles-permissions'),
            ),
            'hierarchical' => true,
            'show_admin_column' => true,
            'rewrite' => array('slug' => 'group-category'),
        ));
    }
    
    /**
     * Register group capabilities
     */
    public function register_group_capabilities() {
        $admin_role = get_role('administrator');
        
        if ($admin_role) {
            // Group capabilities
            $admin_role->add_cap('edit_wrp_group');
            $admin_role->add_cap('edit_wrp_groups');
            $admin_role->add_cap('edit_others_wrp_groups');
            $admin_role->add_cap('publish_wrp_groups');
            $admin_role->add_cap('read_wrp_group');
            $admin_role->add_cap('read_private_wrp_groups');
            $admin_role->add_cap('delete_wrp_group');
            $admin_role->add_cap('delete_wrp_groups');
            
            // Event capabilities
            $admin_role->add_cap('manage_wrp_events');
            $admin_role->add_cap('edit_wrp_event');
            $admin_role->add_cap('edit_wrp_events');
            $admin_role->add_cap('publish_wrp_events');
            $admin_role->add_cap('delete_wrp_event');
            
            // Ministry page capabilities
            $admin_role->add_cap('manage_wrp_ministry_pages');
            $admin_role->add_cap('edit_wrp_ministry_page');
            $admin_role->add_cap('edit_wrp_ministry_pages');
            $admin_role->add_cap('publish_wrp_ministry_pages');
            $admin_role->add_cap('delete_wrp_ministry_page');
            
            // Additional group management capabilities
            $admin_role->add_cap('manage_group_members');
            $admin_role->add_cap('track_group_attendance');
            $admin_role->add_cap('manage_group_donations');
            $admin_role->add_cap('manage_group_finances');
            $admin_role->add_cap('manage_group_communications');
            $admin_role->add_cap('manage_group_documents');
        }
    }
    
    /**
     * Add meta boxes for groups
     */
    public function add_group_meta_boxes() {
        // Members meta box
        add_meta_box(
            'wrp_group_members',
            __('Group Members', 'wp-roles-permissions'),
            array($this, 'render_members_meta_box'),
            self::POST_TYPE,
            'normal',
            'high'
        );
        
        // Events meta box
        add_meta_box(
            'wrp_group_events',
            __('Events', 'wp-roles-permissions'),
            array($this, 'render_events_meta_box'),
            self::POST_TYPE,
            'normal',
            'default'
        );
        
        // Donations meta box
        add_meta_box(
            'wrp_group_donations',
            __('Donations & Finances', 'wp-roles-permissions'),
            array($this, 'render_donations_meta_box'),
            self::POST_TYPE,
            'normal',
            'default'
        );
        
        // Documents meta box
        add_meta_box(
            'wrp_group_documents',
            __('Documents Library', 'wp-roles-permissions'),
            array($this, 'render_documents_meta_box'),
            self::POST_TYPE,
            'normal',
            'default'
        );
        
        // Communications meta box
        add_meta_box(
            'wrp_group_communications',
            __('Communications', 'wp-roles-permissions'),
            array($this, 'render_communications_meta_box'),
            self::POST_TYPE,
            'side',
            'default'
        );
        
        // Ministry Pages meta box
        add_meta_box(
            'wrp_group_ministry_pages',
            __('Ministry Pages', 'wp-roles-permissions'),
            array($this, 'render_ministry_pages_meta_box'),
            self::POST_TYPE,
            'side',
            'default'
        );
        
        // Event-specific meta boxes
        add_meta_box(
            'wrp_event_details',
            __('Event Details', 'wp-roles-permissions'),
            array($this, 'render_event_details_meta_box'),
            self::EVENT_POST_TYPE,
            'normal',
            'high'
        );
        
        add_meta_box(
            'wrp_event_attendance',
            __('Attendance Tracking', 'wp-roles-permissions'),
            array($this, 'render_attendance_meta_box'),
            self::EVENT_POST_TYPE,
            'normal',
            'default'
        );
        
        // Ministry page visibility
        add_meta_box(
            'wrp_ministry_visibility',
            __('Page Visibility', 'wp-roles-permissions'),
            array($this, 'render_ministry_visibility_meta_box'),
            self::MINISTRY_POST_TYPE,
            'side',
            'default'
        );
    }
    
    /**
     * Render members meta box
     */
    public function render_members_meta_box($post) {
        wp_nonce_field('wrp_group_members_meta', 'wrp_group_members_nonce');
        
        $members = get_post_meta($post->ID, '_wrp_group_members', true);
        $members = is_array($members) ? $members : array();
        
        $member_info = get_post_meta($post->ID, '_wrp_member_info', true);
        $member_info = is_array($member_info) ? $member_info : array();
        
        ?>
        <div class="wrp-members-manager">
            <div class="wrp-add-member">
                <h4><?php _e('Add Member', 'wp-roles-permissions'); ?></h4>
                <select id="wrp-member-select" style="width: 60%;">
                    <option value=""><?php _e('Select a user...', 'wp-roles-permissions'); ?></option>
                    <?php
                    $users = get_users(array('orderby' => 'display_name'));
                    foreach ($users as $user) {
                        if (!in_array($user->ID, $members)) {
                            echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . ' (' . esc_html($user->user_email) . ')</option>';
                        }
                    }
                    ?>
                </select>
                <button type="button" class="button" id="wrp-add-member-btn"><?php _e('Add Member', 'wp-roles-permissions'); ?></button>
            </div>
            
            <div class="wrp-members-list" style="margin-top: 20px;">
                <h4><?php _e('Current Members', 'wp-roles-permissions'); ?> (<?php echo count($members); ?>)</h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Email', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Phone', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Role in Group', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Actions', 'wp-roles-permissions'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wrp-members-tbody">
                        <?php
                        if (!empty($members)) {
                            foreach ($members as $member_id) {
                                $user = get_userdata($member_id);
                                if (!$user) continue;
                                
                                $info = isset($member_info[$member_id]) ? $member_info[$member_id] : array();
                                ?>
                                <tr data-member-id="<?php echo esc_attr($member_id); ?>">
                                    <td><?php echo esc_html($user->display_name); ?></td>
                                    <td><?php echo esc_html($user->user_email); ?></td>
                                    <td>
                                        <input type="text" name="member_phone[<?php echo $member_id; ?>]" 
                                               value="<?php echo isset($info['phone']) ? esc_attr($info['phone']) : ''; ?>" 
                                               placeholder="Phone" class="small-text">
                                    </td>
                                    <td>
                                        <input type="text" name="member_role[<?php echo $member_id; ?>]" 
                                               value="<?php echo isset($info['role']) ? esc_attr($info['role']) : ''; ?>" 
                                               placeholder="Role" class="regular-text">
                                    </td>
                                    <td>
                                        <button type="button" class="button wrp-remove-member" data-member-id="<?php echo esc_attr($member_id); ?>">
                                            <?php _e('Remove', 'wp-roles-permissions'); ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="5">' . __('No members yet.', 'wp-roles-permissions') . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <input type="hidden" name="wrp_group_members" id="wrp-group-members-input" value="<?php echo esc_attr(json_encode($members)); ?>">
            </div>
        </div>
        
        <style>
            .wrp-members-manager { margin: 15px 0; }
            .wrp-add-member { padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
            #wrp-member-select { margin-right: 10px; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wrp-add-member-btn').on('click', function() {
                var userId = $('#wrp-member-select').val();
                if (!userId) return;
                
                var members = JSON.parse($('#wrp-group-members-input').val() || '[]');
                members.push(parseInt(userId));
                $('#wrp-group-members-input').val(JSON.stringify(members));
                
                location.reload();
            });
            
            $('.wrp-remove-member').on('click', function() {
                var userId = $(this).data('member-id');
                var members = JSON.parse($('#wrp-group-members-input').val() || '[]');
                members = members.filter(function(id) { return id != userId; });
                $('#wrp-group-members-input').val(JSON.stringify(members));
                
                $(this).closest('tr').remove();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render events meta box
     */
    public function render_events_meta_box($post) {
        $events = get_posts(array(
            'post_type' => self::EVENT_POST_TYPE,
            'meta_key' => '_wrp_event_group',
            'meta_value' => $post->ID,
            'posts_per_page' => -1,
        ));
        
        ?>
        <div class="wrp-events-manager">
            <p>
                <a href="<?php echo admin_url('post-new.php?post_type=' . self::EVENT_POST_TYPE . '&group_id=' . $post->ID); ?>" 
                   class="button button-primary">
                    <?php _e('Add New Event', 'wp-roles-permissions'); ?>
                </a>
            </p>
            
            <?php if (!empty($events)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Event', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Date', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Time', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Attendance', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Actions', 'wp-roles-permissions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): 
                            $event_date = get_post_meta($event->ID, '_wrp_event_date', true);
                            $event_time = get_post_meta($event->ID, '_wrp_event_time', true);
                            $attendance = get_post_meta($event->ID, '_wrp_event_attendance', true);
                            $attendance_count = is_array($attendance) ? count($attendance) : 0;
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($event->post_title); ?></strong></td>
                                <td><?php echo $event_date ? esc_html(date('M j, Y', strtotime($event_date))) : '-'; ?></td>
                                <td><?php echo esc_html($event_time); ?></td>
                                <td><?php echo $attendance_count; ?> attendees</td>
                                <td>
                                    <a href="<?php echo get_edit_post_link($event->ID); ?>" class="button button-small">
                                        <?php _e('Edit', 'wp-roles-permissions'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No events created yet.', 'wp-roles-permissions'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render donations meta box
     */
    public function render_donations_meta_box($post) {
        wp_nonce_field('wrp_group_donations_meta', 'wrp_group_donations_nonce');
        
        $donations = get_post_meta($post->ID, '_wrp_group_donations', true);
        $donations = is_array($donations) ? $donations : array();
        
        $total_donations = 0;
        foreach ($donations as $donation) {
            $total_donations += floatval($donation['amount']);
        }
        
        $expenses = get_post_meta($post->ID, '_wrp_group_expenses', true);
        $expenses = is_array($expenses) ? $expenses : array();
        
        $total_expenses = 0;
        foreach ($expenses as $expense) {
            $total_expenses += floatval($expense['amount']);
        }
        
        $balance = $total_donations - $total_expenses;
        ?>
        <div class="wrp-finances-manager">
            <div class="wrp-finance-summary" style="background: #f0f0f0; padding: 15px; margin-bottom: 20px;">
                <h4><?php _e('Financial Summary', 'wp-roles-permissions'); ?></h4>
                <p><strong><?php _e('Total Donations:', 'wp-roles-permissions'); ?></strong> $<?php echo number_format($total_donations, 2); ?></p>
                <p><strong><?php _e('Total Expenses:', 'wp-roles-permissions'); ?></strong> $<?php echo number_format($total_expenses, 2); ?></p>
                <p><strong><?php _e('Current Balance:', 'wp-roles-permissions'); ?></strong> 
                    <span style="color: <?php echo $balance >= 0 ? 'green' : 'red'; ?>;">
                        $<?php echo number_format($balance, 2); ?>
                    </span>
                </p>
            </div>
            
            <div class="wrp-add-donation">
                <h4><?php _e('Record Donation', 'wp-roles-permissions'); ?></h4>
                <table class="form-table">
                    <tr>
                        <td>
                            <input type="number" step="0.01" id="donation-amount" placeholder="Amount" style="width: 150px;">
                            <input type="text" id="donation-donor" placeholder="Donor Name" style="width: 200px;">
                            <input type="date" id="donation-date" value="<?php echo date('Y-m-d'); ?>" style="width: 150px;">
                            <input type="text" id="donation-notes" placeholder="Notes" style="width: 250px;">
                            <button type="button" class="button" id="wrp-add-donation-btn"><?php _e('Add Donation', 'wp-roles-permissions'); ?></button>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="wrp-add-expense">
                <h4><?php _e('Record Expense', 'wp-roles-permissions'); ?></h4>
                <table class="form-table">
                    <tr>
                        <td>
                            <input type="number" step="0.01" id="expense-amount" placeholder="Amount" style="width: 150px;">
                            <input type="text" id="expense-description" placeholder="Description" style="width: 250px;">
                            <input type="date" id="expense-date" value="<?php echo date('Y-m-d'); ?>" style="width: 150px;">
                            <button type="button" class="button" id="wrp-add-expense-btn"><?php _e('Add Expense', 'wp-roles-permissions'); ?></button>
                        </td>
                    </tr>
                </table>
            </div>
            
            <input type="hidden" name="wrp_group_donations" id="wrp-donations-data" value="<?php echo esc_attr(json_encode($donations)); ?>">
            <input type="hidden" name="wrp_group_expenses" id="wrp-expenses-data" value="<?php echo esc_attr(json_encode($expenses)); ?>">
            
            <h4><?php _e('Recent Transactions', 'wp-roles-permissions'); ?></h4>
            <div id="wrp-transactions-list">
                <?php
                $transactions = array();
                foreach ($donations as $d) {
                    $d['type'] = 'donation';
                    $transactions[] = $d;
                }
                foreach ($expenses as $e) {
                    $e['type'] = 'expense';
                    $transactions[] = $e;
                }
                
                usort($transactions, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                
                if (!empty($transactions)):
                ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'wp-roles-permissions'); ?></th>
                                <th><?php _e('Type', 'wp-roles-permissions'); ?></th>
                                <th><?php _e('Description', 'wp-roles-permissions'); ?></th>
                                <th><?php _e('Amount', 'wp-roles-permissions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($transactions, 0, 10) as $transaction): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($transaction['date'])); ?></td>
                                    <td>
                                        <span class="<?php echo $transaction['type']; ?>-badge">
                                            <?php echo ucfirst($transaction['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        echo $transaction['type'] === 'donation' 
                                            ? esc_html($transaction['donor']) . (isset($transaction['notes']) && $transaction['notes'] ? ' - ' . esc_html($transaction['notes']) : '')
                                            : esc_html($transaction['description']); 
                                        ?>
                                    </td>
                                    <td style="color: <?php echo $transaction['type'] === 'donation' ? 'green' : 'red'; ?>;">
                                        <?php echo $transaction['type'] === 'donation' ? '+' : '-'; ?>$<?php echo number_format($transaction['amount'], 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No transactions recorded yet.', 'wp-roles-permissions'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
            .donation-badge { background: #46b450; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; }
            .expense-badge { background: #dc3232; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wrp-add-donation-btn').on('click', function() {
                var amount = $('#donation-amount').val();
                var donor = $('#donation-donor').val();
                var date = $('#donation-date').val();
                var notes = $('#donation-notes').val();
                
                if (!amount || !donor) {
                    alert('Please enter amount and donor name');
                    return;
                }
                
                var donations = JSON.parse($('#wrp-donations-data').val() || '[]');
                donations.push({
                    amount: amount,
                    donor: donor,
                    date: date,
                    notes: notes,
                    timestamp: new Date().getTime()
                });
                $('#wrp-donations-data').val(JSON.stringify(donations));
                
                location.reload();
            });
            
            $('#wrp-add-expense-btn').on('click', function() {
                var amount = $('#expense-amount').val();
                var description = $('#expense-description').val();
                var date = $('#expense-date').val();
                
                if (!amount || !description) {
                    alert('Please enter amount and description');
                    return;
                }
                
                var expenses = JSON.parse($('#wrp-expenses-data').val() || '[]');
                expenses.push({
                    amount: amount,
                    description: description,
                    date: date,
                    timestamp: new Date().getTime()
                });
                $('#wrp-expenses-data').val(JSON.stringify(expenses));
                
                location.reload();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render documents meta box
     */
    public function render_documents_meta_box($post) {
        wp_nonce_field('wrp_group_documents_meta', 'wrp_group_documents_nonce');
        
        $shared_docs = get_post_meta($post->ID, '_wrp_shared_documents', true);
        $shared_docs = is_array($shared_docs) ? $shared_docs : array();
        
        $private_docs = get_post_meta($post->ID, '_wrp_private_documents', true);
        $private_docs = is_array($private_docs) ? $private_docs : array();
        
        ?>
        <div class="wrp-documents-manager">
            <h4><?php _e('Shared Documents Library', 'wp-roles-permissions'); ?></h4>
            <p class="description"><?php _e('These documents are visible to all group members', 'wp-roles-permissions'); ?></p>
            
            <button type="button" class="button" id="wrp-upload-shared-doc">
                <?php _e('Upload Shared Document', 'wp-roles-permissions'); ?>
            </button>
            
            <div id="wrp-shared-docs-list" style="margin-top: 15px;">
                <?php if (!empty($shared_docs)): ?>
                    <ul class="wrp-docs-list">
                        <?php foreach ($shared_docs as $index => $doc): ?>
                            <li>
                                <a href="<?php echo esc_url($doc['url']); ?>" target="_blank">
                                    <?php echo esc_html($doc['title']); ?>
                                </a>
                                <span class="wrp-doc-meta">
                                    (<?php echo esc_html($doc['type']); ?> - <?php echo date('M j, Y', $doc['uploaded']); ?>)
                                </span>
                                <button type="button" class="button-link-delete wrp-remove-doc" data-type="shared" data-index="<?php echo $index; ?>">
                                    <?php _e('Remove', 'wp-roles-permissions'); ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><em><?php _e('No shared documents yet.', 'wp-roles-permissions'); ?></em></p>
                <?php endif; ?>
            </div>
            
            <hr style="margin: 20px 0;">
            
            <h4><?php _e('Private Documents Library', 'wp-roles-permissions'); ?></h4>
            <p class="description"><?php _e('These documents are only visible to group administrators', 'wp-roles-permissions'); ?></p>
            
            <button type="button" class="button" id="wrp-upload-private-doc">
                <?php _e('Upload Private Document', 'wp-roles-permissions'); ?>
            </button>
            
            <div id="wrp-private-docs-list" style="margin-top: 15px;">
                <?php if (!empty($private_docs)): ?>
                    <ul class="wrp-docs-list">
                        <?php foreach ($private_docs as $index => $doc): ?>
                            <li>
                                <a href="<?php echo esc_url($doc['url']); ?>" target="_blank">
                                    <?php echo esc_html($doc['title']); ?>
                                </a>
                                <span class="wrp-doc-meta">
                                    (<?php echo esc_html($doc['type']); ?> - <?php echo date('M j, Y', $doc['uploaded']); ?>)
                                </span>
                                <button type="button" class="button-link-delete wrp-remove-doc" data-type="private" data-index="<?php echo $index; ?>">
                                    <?php _e('Remove', 'wp-roles-permissions'); ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><em><?php _e('No private documents yet.', 'wp-roles-permissions'); ?></em></p>
                <?php endif; ?>
            </div>
            
            <input type="hidden" name="wrp_shared_documents" id="wrp-shared-docs-data" value="<?php echo esc_attr(json_encode($shared_docs)); ?>">
            <input type="hidden" name="wrp_private_documents" id="wrp-private-docs-data" value="<?php echo esc_attr(json_encode($private_docs)); ?>">
        </div>
        
        <style>
            .wrp-docs-list { list-style: none; padding: 0; }
            .wrp-docs-list li { padding: 8px; background: #f9f9f9; margin-bottom: 5px; border-left: 3px solid #0073aa; }
            .wrp-doc-meta { color: #666; font-size: 12px; margin-left: 10px; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            var fileFrame;
            
            $('#wrp-upload-shared-doc, #wrp-upload-private-doc').on('click', function(e) {
                e.preventDefault();
                var isShared = $(this).attr('id') === 'wrp-upload-shared-doc';
                
                if (fileFrame) {
                    fileFrame.open();
                    return;
                }
                
                fileFrame = wp.media({
                    title: '<?php _e('Select Document', 'wp-roles-permissions'); ?>',
                    button: {
                        text: '<?php _e('Use this document', 'wp-roles-permissions'); ?>'
                    },
                    multiple: false
                });
                
                fileFrame.on('select', function() {
                    var attachment = fileFrame.state().get('selection').first().toJSON();
                    var docData = {
                        title: attachment.title,
                        url: attachment.url,
                        type: attachment.subtype || attachment.type,
                        uploaded: Math.floor(Date.now() / 1000)
                    };
                    
                    if (isShared) {
                        var docs = JSON.parse($('#wrp-shared-docs-data').val() || '[]');
                        docs.push(docData);
                        $('#wrp-shared-docs-data').val(JSON.stringify(docs));
                    } else {
                        var docs = JSON.parse($('#wrp-private-docs-data').val() || '[]');
                        docs.push(docData);
                        $('#wrp-private-docs-data').val(JSON.stringify(docs));
                    }
                    
                    location.reload();
                });
                
                fileFrame.open();
            });
            
            $('.wrp-remove-doc').on('click', function() {
                var type = $(this).data('type');
                var index = $(this).data('index');
                var inputId = type === 'shared' ? '#wrp-shared-docs-data' : '#wrp-private-docs-data';
                
                var docs = JSON.parse($(inputId).val() || '[]');
                docs.splice(index, 1);
                $(inputId).val(JSON.stringify(docs));
                
                $(this).closest('li').remove();
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render communications meta box
     */
    public function render_communications_meta_box($post) {
        wp_nonce_field('wrp_group_communications_meta', 'wrp_group_communications_nonce');
        
        $auto_email = get_post_meta($post->ID, '_wrp_auto_email_enabled', true);
        $email_frequency = get_post_meta($post->ID, '_wrp_email_frequency', true);
        
        ?>
        <div class="wrp-communications-settings">
            <p>
                <label>
                    <input type="checkbox" name="wrp_auto_email_enabled" value="1" <?php checked($auto_email, '1'); ?>>
                    <?php _e('Enable automatic communications', 'wp-roles-permissions'); ?>
                </label>
            </p>
            
            <p>
                <label><?php _e('Email Frequency:', 'wp-roles-permissions'); ?></label><br>
                <select name="wrp_email_frequency" style="width: 100%;">
                    <option value="daily" <?php selected($email_frequency, 'daily'); ?>><?php _e('Daily', 'wp-roles-permissions'); ?></option>
                    <option value="weekly" <?php selected($email_frequency, 'weekly'); ?>><?php _e('Weekly', 'wp-roles-permissions'); ?></option>
                    <option value="monthly" <?php selected($email_frequency, 'monthly'); ?>><?php _e('Monthly', 'wp-roles-permissions'); ?></option>
                </select>
            </p>
            
            <p>
                <button type="button" class="button button-primary" id="wrp-send-group-email">
                    <?php _e('Send Email to Group', 'wp-roles-permissions'); ?>
                </button>
            </p>
            
            <p class="description">
                <?php _e('Automatically send updates and announcements to all group members.', 'wp-roles-permissions'); ?>
            </p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wrp-send-group-email').on('click', function() {
                alert('<?php _e('Email composition feature coming soon!', 'wp-roles-permissions'); ?>');
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render ministry pages meta box
     */
    public function render_ministry_pages_meta_box($post) {
        $pages = get_posts(array(
            'post_type' => self::MINISTRY_POST_TYPE,
            'meta_key' => '_wrp_ministry_group',
            'meta_value' => $post->ID,
            'posts_per_page' => -1,
        ));
        
        ?>
        <div class="wrp-ministry-pages">
            <p>
                <a href="<?php echo admin_url('post-new.php?post_type=' . self::MINISTRY_POST_TYPE . '&group_id=' . $post->ID); ?>" 
                   class="button button-primary button-small">
                    <?php _e('Add Ministry Page', 'wp-roles-permissions'); ?>
                </a>
            </p>
            
            <?php if (!empty($pages)): ?>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <?php foreach ($pages as $page): 
                        $visibility = get_post_meta($page->ID, '_wrp_ministry_visibility', true);
                    ?>
                        <li>
                            <a href="<?php echo get_edit_post_link($page->ID); ?>">
                                <?php echo esc_html($page->post_title); ?>
                            </a>
                            <br>
                            <small style="color: #666;">
                                <?php echo $visibility === 'public' ? __('Public', 'wp-roles-permissions') : __('Private', 'wp-roles-permissions'); ?>
                            </small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><em><?php _e('No ministry pages yet.', 'wp-roles-permissions'); ?></em></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render event details meta box
     */
    public function render_event_details_meta_box($post) {
        wp_nonce_field('wrp_event_details_meta', 'wrp_event_details_nonce');
        
        $event_date = get_post_meta($post->ID, '_wrp_event_date', true);
        $event_time = get_post_meta($post->ID, '_wrp_event_time', true);
        $event_location = get_post_meta($post->ID, '_wrp_event_location', true);
        $event_group = get_post_meta($post->ID, '_wrp_event_group', true);
        
        // Set group from URL parameter if creating new event
        if (!$event_group && isset($_GET['group_id'])) {
            $event_group = intval($_GET['group_id']);
        }
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="wrp_event_group"><?php _e('Group', 'wp-roles-permissions'); ?></label></th>
                <td>
                    <select name="wrp_event_group" id="wrp_event_group" style="width: 100%;">
                        <?php
                        $groups = get_posts(array('post_type' => self::POST_TYPE, 'posts_per_page' => -1));
                        foreach ($groups as $group) {
                            echo '<option value="' . $group->ID . '" ' . selected($event_group, $group->ID, false) . '>' . esc_html($group->post_title) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wrp_event_date"><?php _e('Event Date', 'wp-roles-permissions'); ?></label></th>
                <td>
                    <input type="date" name="wrp_event_date" id="wrp_event_date" 
                           value="<?php echo esc_attr($event_date); ?>" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <th><label for="wrp_event_time"><?php _e('Event Time', 'wp-roles-permissions'); ?></label></th>
                <td>
                    <input type="time" name="wrp_event_time" id="wrp_event_time" 
                           value="<?php echo esc_attr($event_time); ?>" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <th><label for="wrp_event_location"><?php _e('Location', 'wp-roles-permissions'); ?></label></th>
                <td>
                    <input type="text" name="wrp_event_location" id="wrp_event_location" 
                           value="<?php echo esc_attr($event_location); ?>" class="regular-text" style="width: 100%;">
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render attendance meta box
     */
    public function render_attendance_meta_box($post) {
        wp_nonce_field('wrp_event_attendance_meta', 'wrp_event_attendance_nonce');
        
        $group_id = get_post_meta($post->ID, '_wrp_event_group', true);
        $attendance = get_post_meta($post->ID, '_wrp_event_attendance', true);
        $attendance = is_array($attendance) ? $attendance : array();
        
        if (!$group_id) {
            echo '<p>' . __('Please select a group for this event first.', 'wp-roles-permissions') . '</p>';
            return;
        }
        
        $members = get_post_meta($group_id, '_wrp_group_members', true);
        $members = is_array($members) ? $members : array();
        
        ?>
        <div class="wrp-attendance-tracker">
            <p><strong><?php _e('Mark Attendance:', 'wp-roles-permissions'); ?></strong></p>
            
            <?php if (!empty($members)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th><?php _e('Member', 'wp-roles-permissions'); ?></th>
                            <th><?php _e('Status', 'wp-roles-permissions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member_id): 
                            $user = get_userdata($member_id);
                            if (!$user) continue;
                            
                            $attended = isset($attendance[$member_id]);
                        ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="wrp_attendance[<?php echo $member_id; ?>]" 
                                           value="1" <?php checked($attended); ?>>
                                </td>
                                <td><?php echo esc_html($user->display_name); ?></td>
                                <td>
                                    <?php if ($attended): ?>
                                        <span style="color: green;">✓ <?php _e('Attended', 'wp-roles-permissions'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #999;">- <?php _e('Not marked', 'wp-roles-permissions'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="description" style="margin-top: 10px;">
                    <?php 
                    $attendance_count = count($attendance);
                    $total_members = count($members);
                    $percentage = $total_members > 0 ? round(($attendance_count / $total_members) * 100) : 0;
                    
                    printf(
                        __('Attendance: %d of %d members (%d%%)', 'wp-roles-permissions'),
                        $attendance_count,
                        $total_members,
                        $percentage
                    );
                    ?>
                </p>
            <?php else: ?>
                <p><?php _e('No members in this group yet.', 'wp-roles-permissions'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render ministry visibility meta box
     */
    public function render_ministry_visibility_meta_box($post) {
        wp_nonce_field('wrp_ministry_visibility_meta', 'wrp_ministry_visibility_nonce');
        
        $visibility = get_post_meta($post->ID, '_wrp_ministry_visibility', true);
        $group_id = get_post_meta($post->ID, '_wrp_ministry_group', true);
        
        // Set group from URL parameter if creating new page
        if (!$group_id && isset($_GET['group_id'])) {
            $group_id = intval($_GET['group_id']);
        }
        
        ?>
        <p>
            <label for="wrp_ministry_group"><strong><?php _e('Group:', 'wp-roles-permissions'); ?></strong></label><br>
            <select name="wrp_ministry_group" id="wrp_ministry_group" style="width: 100%;">
                <?php
                $groups = get_posts(array('post_type' => self::POST_TYPE, 'posts_per_page' => -1));
                foreach ($groups as $group) {
                    echo '<option value="' . $group->ID . '" ' . selected($group_id, $group->ID, false) . '>' . esc_html($group->post_title) . '</option>';
                }
                ?>
            </select>
        </p>
        
        <p>
            <label><strong><?php _e('Visibility:', 'wp-roles-permissions'); ?></strong></label><br>
            <label>
                <input type="radio" name="wrp_ministry_visibility" value="private" <?php checked($visibility, 'private'); ?> <?php checked($visibility, ''); ?>>
                <?php _e('Private (Group Members Only)', 'wp-roles-permissions'); ?>
            </label><br>
            <label>
                <input type="radio" name="wrp_ministry_visibility" value="public" <?php checked($visibility, 'public'); ?>>
                <?php _e('Public (Visible to Everyone)', 'wp-roles-permissions'); ?>
            </label>
        </p>
        
        <p class="description">
            <?php _e('Private pages are only accessible to group members. Public pages can be viewed by anyone.', 'wp-roles-permissions'); ?>
        </p>
        <?php
    }
    
    /**
     * Save meta boxes data
     */
    public function save_group_meta_boxes($post_id, $post) {
        // Check if this is the right post type
        if (!in_array($post->post_type, array(self::POST_TYPE, self::EVENT_POST_TYPE, self::MINISTRY_POST_TYPE))) {
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
        
        // Save group members
        if (isset($_POST['wrp_group_members_nonce']) && wp_verify_nonce($_POST['wrp_group_members_nonce'], 'wrp_group_members_meta')) {
            if (isset($_POST['wrp_group_members'])) {
                $members = json_decode(stripslashes($_POST['wrp_group_members']), true);
                update_post_meta($post_id, '_wrp_group_members', $members);
                
                // Save member info
                $member_info = array();
                if (isset($_POST['member_phone'])) {
                    foreach ($_POST['member_phone'] as $user_id => $phone) {
                        $member_info[$user_id]['phone'] = sanitize_text_field($phone);
                    }
                }
                if (isset($_POST['member_role'])) {
                    foreach ($_POST['member_role'] as $user_id => $role) {
                        $member_info[$user_id]['role'] = sanitize_text_field($role);
                    }
                }
                update_post_meta($post_id, '_wrp_member_info', $member_info);
            }
        }
        
        // Save donations and expenses
        if (isset($_POST['wrp_group_donations_nonce']) && wp_verify_nonce($_POST['wrp_group_donations_nonce'], 'wrp_group_donations_meta')) {
            if (isset($_POST['wrp_group_donations'])) {
                $donations = json_decode(stripslashes($_POST['wrp_group_donations']), true);
                update_post_meta($post_id, '_wrp_group_donations', $donations);
            }
            if (isset($_POST['wrp_group_expenses'])) {
                $expenses = json_decode(stripslashes($_POST['wrp_group_expenses']), true);
                update_post_meta($post_id, '_wrp_group_expenses', $expenses);
            }
        }
        
        // Save documents
        if (isset($_POST['wrp_group_documents_nonce']) && wp_verify_nonce($_POST['wrp_group_documents_nonce'], 'wrp_group_documents_meta')) {
            if (isset($_POST['wrp_shared_documents'])) {
                $shared_docs = json_decode(stripslashes($_POST['wrp_shared_documents']), true);
                update_post_meta($post_id, '_wrp_shared_documents', $shared_docs);
            }
            if (isset($_POST['wrp_private_documents'])) {
                $private_docs = json_decode(stripslashes($_POST['wrp_private_documents']), true);
                update_post_meta($post_id, '_wrp_private_documents', $private_docs);
            }
        }
        
        // Save communications settings
        if (isset($_POST['wrp_group_communications_nonce']) && wp_verify_nonce($_POST['wrp_group_communications_nonce'], 'wrp_group_communications_meta')) {
            $auto_email = isset($_POST['wrp_auto_email_enabled']) ? '1' : '0';
            update_post_meta($post_id, '_wrp_auto_email_enabled', $auto_email);
            
            if (isset($_POST['wrp_email_frequency'])) {
                update_post_meta($post_id, '_wrp_email_frequency', sanitize_text_field($_POST['wrp_email_frequency']));
            }
        }
        
        // Save event details
        if (isset($_POST['wrp_event_details_nonce']) && wp_verify_nonce($_POST['wrp_event_details_nonce'], 'wrp_event_details_meta')) {
            if (isset($_POST['wrp_event_group'])) {
                update_post_meta($post_id, '_wrp_event_group', intval($_POST['wrp_event_group']));
            }
            if (isset($_POST['wrp_event_date'])) {
                update_post_meta($post_id, '_wrp_event_date', sanitize_text_field($_POST['wrp_event_date']));
            }
            if (isset($_POST['wrp_event_time'])) {
                update_post_meta($post_id, '_wrp_event_time', sanitize_text_field($_POST['wrp_event_time']));
            }
            if (isset($_POST['wrp_event_location'])) {
                update_post_meta($post_id, '_wrp_event_location', sanitize_text_field($_POST['wrp_event_location']));
            }
        }
        
        // Save event attendance
        if (isset($_POST['wrp_event_attendance_nonce']) && wp_verify_nonce($_POST['wrp_event_attendance_nonce'], 'wrp_event_attendance_meta')) {
            $attendance = array();
            if (isset($_POST['wrp_attendance']) && is_array($_POST['wrp_attendance'])) {
                foreach ($_POST['wrp_attendance'] as $user_id => $value) {
                    if ($value == '1') {
                        $attendance[intval($user_id)] = array(
                            'attended' => true,
                            'timestamp' => current_time('mysql')
                        );
                    }
                }
            }
            update_post_meta($post_id, '_wrp_event_attendance', $attendance);
        }
        
        // Save ministry page settings
        if (isset($_POST['wrp_ministry_visibility_nonce']) && wp_verify_nonce($_POST['wrp_ministry_visibility_nonce'], 'wrp_ministry_visibility_meta')) {
            if (isset($_POST['wrp_ministry_group'])) {
                update_post_meta($post_id, '_wrp_ministry_group', intval($_POST['wrp_ministry_group']));
            }
            if (isset($_POST['wrp_ministry_visibility'])) {
                update_post_meta($post_id, '_wrp_ministry_visibility', sanitize_text_field($_POST['wrp_ministry_visibility']));
            }
        }
    }
    
    /**
     * Get group members
     */
    public function get_group_members($group_id) {
        $members = get_post_meta($group_id, '_wrp_group_members', true);
        return is_array($members) ? $members : array();
    }
    
    /**
     * Check if user is group member
     */
    public function is_group_member($group_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $members = $this->get_group_members($group_id);
        return in_array($user_id, $members);
    }
    
    /**
     * Check if user is group creator
     */
    public function is_group_creator($group_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $post = get_post($group_id);
        return $post && $post->post_author == $user_id;
    }
}

# WP Roles & Permissions

A comprehensive WordPress plugin for advanced role and permission management. Create custom roles, assign them to users, and restrict content access based on roles.

## Features

### Role Management
- **Create Custom Roles**: Define new user roles with specific capabilities
- **Edit Roles**: Modify existing custom roles and their capabilities
- **Delete Roles**: Remove custom roles (with automatic user reassignment)
- **Capability Control**: Granular control over what each role can do

### User Management
- **Assign Roles**: Add roles to users individually or in bulk
- **Multiple Roles**: Users can have multiple roles simultaneously
- **Role Replacement**: Option to replace all existing roles with a new one
- **Automatic Fallback**: Users are assigned Subscriber role if left without any role

### Content Restriction
- **Post & Page Protection**: Restrict access to individual posts and pages
- **Role-Based Access**: Only users with assigned roles can view restricted content
- **Public by Default**: Content is public unless roles are explicitly assigned
- **Smart Filtering**: Automatically hides restricted content from archives and search

### Security & UX
- **Admin Override**: Administrators always have access to all content
- **Login Redirection**: Non-logged users are redirected to login for restricted content
- **Access Denied Messages**: Clear feedback when users lack permissions
- **Nonce Protection**: All forms secured with WordPress nonces

## Installation

See [INSTALLATION.md](INSTALLATION.md) for detailed installation instructions.

**Quick Start:**
1. Upload the plugin to `/wp-content/plugins/wp-roles-permissions/`
2. Activate through the WordPress 'Plugins' menu
3. Access via 'Roles & Permissions' in the admin menu

## Usage

See [USER-GUIDE.md](USER-GUIDE.md) for comprehensive usage instructions.

**Quick Overview:**

### Creating a Role
1. Go to **Roles & Permissions > Manage Roles**
2. Enter role slug and name
3. Select capabilities
4. Click **Create Role**

### Assigning Roles to Users
1. Go to **Roles & Permissions > Assign User Roles**
2. Click **Manage Roles** next to a user
3. Select a role and click **Add Role**

### Restricting Content
1. Edit a post or page
2. Find the **Content Access Restrictions** meta box
3. Check the roles that should have access
4. Publish/Update the post

## How It Works

### Content Access Logic

1. **Public Content**: No roles selected = everyone can access
2. **Restricted Content**: One or more roles selected = only users with matching roles can access
3. **Administrator Access**: Admins can always access everything

### Frontend Behavior

- **Not Logged In**: Restricted content hidden from lists, direct access redirects to login
- **Logged In (No Access)**: Restricted content hidden from lists, direct access shows "Access Denied"
- **Logged In (Has Access)**: Normal content display

### WordPress Integration

The plugin hooks into WordPress core:
- `add_meta_boxes`: Adds role selection to post/page editor
- `save_post`: Saves role assignments
- `the_content`: Filters content on display
- `pre_get_posts`: Filters restricted posts from queries
- `template_redirect`: Checks access on single post views

## Requirements

- WordPress 5.0+
- PHP 7.0+
- Administrator privileges

## File Structure

```
wp-roles-permissions/
├── wp-roles-permissions.php          # Main plugin file
├── includes/
│   ├── class-role-manager.php        # Role CRUD operations
│   ├── class-content-restriction.php # Content access control
│   └── class-admin-interface.php     # Admin UI
├── README.md                          # This file
├── INSTALLATION.md                    # Installation guide
└── USER-GUIDE.md                      # Detailed user guide
```

## Plugin Architecture

### Main Classes

**WP_Roles_Permissions**: Main plugin class, initializes all components

**WP_Roles_Permissions_Role_Manager**: 
- Creates, updates, and deletes roles
- Manages user role assignments
- Tracks custom roles

**WP_Roles_Permissions_Content_Restriction**:
- Adds meta boxes for role assignment
- Enforces content access rules
- Filters queries and content

**WP_Roles_Permissions_Admin_Interface**:
- Provides admin UI for role management
- Handles user role assignment interface
- Processes form submissions

## Security

- All admin forms protected with WordPress nonces
- Only administrators can manage roles
- Capability checks on all operations
- SQL injection prevention via prepared statements
- XSS prevention via proper escaping

## Compatibility

- Works with standard WordPress themes
- Compatible with most WordPress plugins
- Respects WordPress coding standards
- Uses WordPress core functions exclusively

## Development

### Coding Standards

- Follows WordPress PHP Coding Standards
- Uses WordPress core functions and APIs
- Properly escaped and sanitized data
- Internationalization ready (text domain: `wp-roles-permissions`)

### Hooks and Filters

The plugin uses standard WordPress hooks:
- Action hooks: `init`, `admin_menu`, `add_meta_boxes`, `save_post`, `template_redirect`, `pre_get_posts`
- Filter hooks: `the_content`, `posts_where`

## Known Limitations

- Cannot edit WordPress default roles (Administrator, Editor, Author, Contributor, Subscriber)
- Cannot delete WordPress default roles
- Content restrictions apply to posts and pages only (can be extended to custom post types)
- Search filtering depends on theme using WordPress query properly

## Future Enhancements

Potential features for future versions:
- Support for custom post types
- Bulk role assignment
- Role templates
- Import/export roles
- Activity logging
- REST API endpoints
- Advanced capability management UI

## License

GPL v2 or later

## Author

billaking

## Support

For issues and questions:
- GitHub: https://github.com/billaking/wp-roles-permissions
- Review the USER-GUIDE.md for detailed usage instructions

## Changelog

### Version 1.0.0 (2025-12-03)
- Initial release
- Role management (create, edit, delete)
- User role assignment
- Content restriction for posts and pages
- Admin interface
- Security features 

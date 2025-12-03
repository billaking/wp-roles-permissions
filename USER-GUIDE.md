# WP Roles & Permissions - User Guide

## Overview

WP Roles & Permissions is a comprehensive WordPress plugin that allows administrators to:

- Create, edit, and delete custom user roles
- Assign roles to users with granular control
- Restrict access to posts and pages based on user roles
- Control content visibility automatically

## Table of Contents

1. [Managing Roles](#managing-roles)
2. [Assigning Roles to Users](#assigning-roles-to-users)
3. [Restricting Content Access](#restricting-content-access)
4. [Understanding Content Restrictions](#understanding-content-restrictions)

---

## Managing Roles

### Creating a New Role

1. Navigate to **Roles & Permissions > Manage Roles** in the WordPress admin
2. Fill in the form on the left side:
   - **Role Slug**: A unique identifier (lowercase letters, numbers, underscores only)
   - **Role Name**: Display name for the role
   - **Capabilities**: Check the capabilities you want to grant to this role
3. Click **Create Role**

**Example:**
- Role Slug: `premium_member`
- Role Name: `Premium Member`
- Capabilities: Select relevant capabilities like `read`, `edit_posts`, etc.

### Editing an Existing Role

1. In the **Custom Roles** table, click **Edit** next to the role you want to modify
2. Update the role name and capabilities
3. Click **Update Role**

**Note:** You cannot edit WordPress default roles (Administrator, Editor, Author, Contributor, Subscriber) to maintain system integrity.

### Deleting a Role

1. In the **Custom Roles** table, click **Delete** next to the role
2. Confirm the deletion
3. Users with this role will be automatically reassigned to the Subscriber role

---

## Assigning Roles to Users

### Viewing Users

1. Navigate to **Roles & Permissions > Assign User Roles**
2. You'll see a table with all users and their current roles

### Managing a User's Roles

1. Click **Manage Roles** next to a user
2. You'll see their current roles listed

### Adding a Role to a User

1. On the user management page, go to the **Add Role** section
2. Select a role from the dropdown
3. (Optional) Check **Replace existing roles** to remove all current roles
4. Click **Add Role**

### Removing a Role from a User

1. In the **Current Roles** section, click **Remove** next to the role you want to remove
2. The role will be removed immediately
3. If the user has no remaining roles, they'll be assigned the Subscriber role automatically

---

## Restricting Content Access

### Assigning Roles to Posts/Pages

1. Edit any post or page
2. Look for the **Content Access Restrictions** meta box in the sidebar
3. Check the roles that should have access to this content
4. Publish or update the post/page

**Important:** 
- If no roles are selected, the content is **publicly accessible**
- Once you select one or more roles, **only users with those roles can access the content**
- Administrators always have access to all content

### Example Use Cases

**Premium Content:**
- Create a `premium_member` role
- Assign it to paying customers
- Restrict premium posts to the `premium_member` role

**Internal Documentation:**
- Create an `internal_staff` role
- Assign it to employees
- Restrict internal pages to the `internal_staff` role

**Multi-tier Access:**
- Create roles like `basic_member`, `premium_member`, `vip_member`
- Assign both `premium_member` and `vip_member` to premium content
- Only assign `vip_member` to exclusive content

---

## Understanding Content Restrictions

### How Content Restrictions Work

1. **Public Content (Default)**
   - No roles selected in the meta box
   - Everyone can view the content
   - Appears in search results and listings

2. **Restricted Content**
   - One or more roles selected
   - Only logged-in users with matching roles can view
   - Non-logged-in users are redirected to login
   - Logged-in users without proper roles see an access denied message

3. **Administrator Override**
   - Administrators can always access all content
   - Useful for content review and management

### Frontend Behavior

**For Non-Logged-In Users:**
- Restricted content is hidden from lists and archives
- Direct access redirects to the login page
- After login, they're redirected back to the content (if they have access)

**For Logged-In Users Without Access:**
- Restricted content is hidden from lists and archives
- Direct access shows an "Access Denied" message
- They're informed to contact an administrator

**For Logged-In Users With Access:**
- Content displays normally
- Full access to read and interact (based on other capabilities)

### Content Visibility in WordPress

The plugin automatically:
- Filters posts from archives and search results if the user doesn't have access
- Prevents unauthorized direct access to restricted content
- Displays appropriate messages based on user status
- Maintains normal WordPress functionality for authorized users

---

## Best Practices

### Role Design

1. **Keep it Simple**: Don't create too many roles; group similar permissions
2. **Descriptive Names**: Use clear, descriptive names for roles and capabilities
3. **Test Access**: Always test as a user with the role to verify access works correctly

### Content Protection

1. **Strategic Restrictions**: Not all content needs restrictions
2. **Clear Communication**: Let users know why they can't access content
3. **Member Pages**: Create a page showing what premium members get

### User Management

1. **Regular Audits**: Periodically review user roles
2. **Onboarding**: Assign roles immediately when users join
3. **Offboarding**: Remove or change roles when users leave

---

## Troubleshooting

### Users Can't See Restricted Content

1. Verify the user has the correct role assigned
2. Check the post/page meta box settings
3. Ensure the role hasn't been deleted
4. Clear any caching plugins

### Can't Delete a Role

- You cannot delete WordPress default roles
- Only custom roles created by this plugin can be deleted

### Content Still Appears in Search

- Search results are filtered, but some themes may bypass this
- Consider using a search plugin that respects WordPress query filters

---

## Security Notes

- All forms use WordPress nonces for security
- Only administrators can manage roles and permissions
- User role changes are logged in the WordPress activity
- Content restrictions are enforced at the WordPress core level

---

## Support

For issues or questions:
1. Check this documentation first
2. Review the plugin code on GitHub
3. Open an issue on the GitHub repository

---

## Version

Current Version: 1.0.0

Last Updated: 2025-12-03

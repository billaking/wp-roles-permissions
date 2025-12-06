# Post Templates Feature

## Overview

The Post Templates feature allows you to create custom templates for WordPress posts with role-based editing permissions. This feature provides an alternative to WordPress's default single post template.

## Key Features

- **Default Group Posts Template**: A pre-configured template that comes with the plugin
- **Custom Templates**: Create unlimited custom templates with unique layouts
- **Shortcode System**: Use shortcodes to display post data dynamically
- **Permission-Based Editing**: Control who can edit each template
- **Per-Post Selection**: Choose which template to use for individual posts

## Using Templates

### Selecting a Template for a Post

1. Edit any post in WordPress
2. Look for the **Post Template** meta box in the sidebar
3. Select your desired template from the dropdown
4. Publish or update the post

The selected template will be used when viewing that post on the frontend.

### Managing Templates

Navigate to **Roles & Permissions > Post Templates** in the WordPress admin.

#### Creating a New Template

1. Click on the Templates menu
2. Fill in the form:
   - **Template Slug**: Unique identifier (lowercase, underscores allowed)
   - **Template Name**: Display name
   - **Description**: Brief description of the template
   - **Edit Capability**: Who can edit this template
   - **Template Content**: HTML with shortcodes
3. Click "Create Template"

#### Editing a Template

1. Click the "Edit" button next to any template
2. Modify the template content
3. Click "Update Template"

**Note**: Users can only edit templates if they have the required capability.

#### Deleting a Template

1. Click the "Delete" button next to any template (except Default Group Posts)
2. Confirm the deletion

**Note**: The Default Group Posts template cannot be deleted.

## Available Shortcodes

Use these shortcodes in your template content to display post data:

### Basic Post Data

- `[post_id]` - Displays the post ID
- `[post_title]` - Displays the post title
- `[post_content]` - Displays the full post content
- `[post_excerpt]` - Displays the post excerpt
- `[post_class]` - Displays post CSS classes for styling

### Post Metadata

- `[post_date]` - Displays the post date
  - Optional parameter: `format="Y-m-d"` (default uses site settings)
  
- `[post_author]` - Displays the post author
  - Optional parameter: `link="yes"` (default) or `link="no"`

### Taxonomies

- `[post_categories]` - Displays post categories
  - Optional parameters:
    - `separator=", "` (default)
    - `label="Categories: "` (default)
    
- `[post_tags]` - Displays post tags
  - Optional parameters:
    - `separator=", "` (default)
    - `label="Tags: "` (default)

### Media and Links

- `[post_thumbnail]` - Displays the featured image
  - Optional parameters:
    - `size="post-thumbnail"` (default) or any registered image size
    - `class=""` - CSS class for the image

- `[post_edit_link]` - Displays an edit link (if user has permission)
  - Optional parameters:
    - `text="Edit"` (default)
    - `before="<span class=\"edit-link\">"` (default)
    - `after="</span>"` (default)

### Comments

- `[post_comments]` - Displays the comments section

## Example Template

```html
<article id="post-[post_id]" class="[post_class]">
    <header class="entry-header">
        [post_thumbnail size="large" class="featured-image"]
        <h1 class="entry-title">[post_title]</h1>
        <div class="entry-meta">
            <span class="posted-on">Published on [post_date format="F j, Y"]</span>
            <span class="byline">by [post_author link="yes"]</span>
        </div>
    </header>
    
    <div class="entry-content">
        [post_content]
    </div>
    
    <footer class="entry-footer">
        <div class="post-meta">
            [post_categories separator=" | " label="Filed under: "]
            [post_tags separator=", " label="Tagged: "]
        </div>
        [post_edit_link text="Edit this post"]
    </footer>
</article>

<div class="comments-section">
    [post_comments]
</div>
```

## Permission Levels

Templates can be restricted to different user roles:

- **Administrators Only** (`manage_options`) - Only site administrators
- **Editors and Above** (`edit_pages`) - Editors and administrators
- **Authors and Above** (`edit_posts`) - Authors, editors, and administrators

## Default Group Posts Template

The plugin includes a default template called "Default Group Posts" with:

- Standard post header with title, date, and author
- Full post content
- Categories and tags
- Edit link (for authorized users)
- Comments section

This template serves as a starting point and can be customized to your needs.

## Technical Notes

- Templates are stored in the WordPress options table
- Template files are generated dynamically when a post is viewed
- Shortcodes are processed using WordPress's `do_shortcode()` function
- All output is properly escaped for security
- Templates include `get_header()` and `get_footer()` calls automatically

## Troubleshooting

### Template not appearing on frontend

1. Ensure the template is assigned to the post
2. Clear any caching plugins
3. Check file permissions on the `templates/` directory

### Shortcode not working

1. Verify the shortcode name is correct
2. Check that you're using the correct syntax
3. Some shortcodes require the post to have specific data (e.g., `[post_thumbnail]` needs a featured image)

### Cannot edit template

Check that your user account has the required capability for that template.

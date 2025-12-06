# Group Rights & Management

## Overview

The Group Rights feature enables group creators to manage comprehensive ministry groups with full administrative capabilities. Each group functions as a complete management system with events, attendance tracking, financial management, member organization, communications, document libraries, and custom ministry pages.

## Key Features

### 1. **Group Management**
Create and manage ministry groups with complete control over:
- Group information and description
- Member roster
- Group categories
- Featured images

### 2. **Member Management**
Organize and track detailed member information:
- Add/remove members
- Store contact information (email, phone)
- Assign roles within the group (e.g., Leader, Secretary, Treasurer)
- View member participation history

### 3. **Event Management**
Create and manage group events with:
- Event title and description
- Date and time scheduling
- Location information
- Featured images
- Automatic linking to parent group

### 4. **Attendance Tracking**
Track member participation:
- Mark attendance for each event
- View attendance statistics
- Calculate attendance percentages
- Track individual member attendance history
- Real-time attendance counts

### 5. **Donations & Financial Management**
Complete financial tracking system:
- Record donations with donor information
- Track expenses with descriptions
- Automatic balance calculation
- Transaction history with dates
- Financial summary dashboard
- Categorized income and expenses

### 6. **Document Libraries**

#### Shared Documents Library
- Upload and manage documents visible to all group members
- Support for all file types (PDF, Word, Excel, images, etc.)
- Document metadata (upload date, file type)
- Easy document removal

#### Private Documents Library
- Restricted access for group administrators only
- Secure storage for sensitive information
- Same file type support as shared library
- Separate from public-facing documents

### 7. **Automatic Communications**
Streamlined group communication:
- Enable/disable automatic emails
- Configurable email frequency (daily, weekly, monthly)
- Send manual emails to all group members
- Automated updates and announcements

### 8. **Custom Ministry Pages**
Create custom web pages for your ministry:
- Full page editor with WordPress visual editor
- Public or private visibility settings
- Link directly to parent group
- Featured images and media support
- SEO-friendly URLs (yoursite.com/ministry/page-name)

## Creating a Group

### Step 1: Navigate to Groups
1. Go to WordPress Admin Dashboard
2. Click on **Roles & Permissions** in the sidebar
3. Select **Groups**
4. Click **Add New Group**

### Step 2: Fill in Group Information
1. **Title**: Enter the group name
2. **Description**: Add detailed information about the group
3. **Category**: Select or create a group category
4. **Featured Image**: Upload a group photo or logo

### Step 3: Add Members
1. Scroll to the **Group Members** section
2. Select a user from the dropdown
3. Click **Add Member**
4. Fill in member details:
   - Phone number
   - Role in group
5. Repeat for all members

### Step 4: Configure Communications
1. In the **Communications** sidebar
2. Check **Enable automatic communications**
3. Select email frequency
4. Save the group

### Step 5: Publish
Click **Publish** to make the group active

## Managing Events

### Creating an Event
1. Open your group in the editor
2. In the **Events** section, click **Add New Event**
3. Fill in event details:
   - Event title
   - Description
   - Date and time
   - Location
4. The group is automatically linked
5. Publish the event

### Tracking Attendance
1. Edit the event
2. Scroll to **Attendance Tracking** section
3. Check the box next to each attendee's name
4. Click **Update** to save attendance
5. View attendance statistics at the top

### Viewing Event List
All events appear in the group's Events section with:
- Event name
- Date and time
- Current attendance count
- Quick edit links

## Financial Management

### Recording Donations
1. Open the group editor
2. Go to **Donations & Finances** section
3. Enter donation details:
   - Amount
   - Donor name
   - Date
   - Notes (optional)
4. Click **Add Donation**
5. Donation appears in transaction history

### Recording Expenses
1. In the same **Donations & Finances** section
2. Enter expense details:
   - Amount
   - Description
   - Date
3. Click **Add Expense**
4. Expense appears in transaction history

### Viewing Financial Summary
The financial summary shows:
- **Total Donations**: Sum of all donations
- **Total Expenses**: Sum of all expenses
- **Current Balance**: Net amount (green if positive, red if negative)
- **Recent Transactions**: Last 10 transactions with type indicators

## Document Management

### Uploading Shared Documents
1. In **Documents Library** section
2. Click **Upload Shared Document**
3. Select file from media library or upload new
4. Document appears in shared list
5. All group members can view

### Uploading Private Documents
1. In **Documents Library** section
2. Click **Upload Private Document**
3. Select file from media library or upload new
4. Document appears in private list
5. Only group administrators can view

### Managing Documents
- Click document title to open/download
- View upload date and file type
- Click **Remove** to delete document
- Documents stored in WordPress media library

## Ministry Pages

### Creating a Ministry Page
1. In group editor, find **Ministry Pages** sidebar
2. Click **Add Ministry Page**
3. Create page content:
   - Page title
   - Full content with visual editor
   - Featured image
   - Select parent group
   - Choose visibility (Public or Private)
4. Publish the page

### Visibility Settings

#### Private Pages
- Visible only to group members
- Access restricted via membership check
- Ideal for internal resources, meeting notes, etc.

#### Public Pages
- Visible to everyone on the website
- Accessible via public URLs
- Perfect for ministry information, testimonies, outreach

### Viewing Ministry Pages
All ministry pages for a group are listed in the sidebar with:
- Page title
- Visibility status
- Quick edit links

## Group Creator Capabilities

Group creators automatically receive these capabilities:
- ✅ Create and edit groups
- ✅ Manage group members
- ✅ Create and manage events
- ✅ Track attendance
- ✅ Record donations and expenses
- ✅ View financial reports
- ✅ Upload and manage documents (shared & private)
- ✅ Send communications to members
- ✅ Create ministry pages
- ✅ Control page visibility
- ✅ Delete events and pages

## Permission Structure

### Administrators
- Full access to all groups
- Can manage any group's settings
- Access to all financial data
- Can view private documents

### Group Creators/Leaders
- Full access to their own groups
- Manage their group members
- Create events for their groups
- Track attendance for their events
- Manage finances for their groups
- Upload documents to their groups
- Create ministry pages for their groups

### Group Members
- View shared documents
- See public ministry pages
- Receive group communications
- Appear in attendance tracking

### Public Users
- View public ministry pages
- No access to internal group features

## Use Cases

### Church Small Groups
- Track weekly attendance
- Share study materials
- Manage group expenses (meals, supplies)
- Create public group page

### Ministry Teams
- Organize volunteer schedules
- Track ministry finances
- Share training documents privately
- Communicate with team members

### Youth Groups
- Event planning and attendance
- Parent communications
- Financial accountability
- Public ministry showcase

### Outreach Ministries
- Track donor contributions
- Manage outreach expenses
- Document impact stories
- Public ministry pages

## Best Practices

### Member Management
- Keep contact information updated
- Assign clear roles to members
- Regularly review membership

### Event Planning
- Create events in advance
- Include detailed location information
- Track attendance consistently
- Review attendance patterns

### Financial Tracking
- Record all transactions promptly
- Include detailed notes
- Regular financial reviews
- Maintain documentation

### Document Organization
- Use clear, descriptive file names
- Separate shared and private appropriately
- Remove outdated documents
- Keep libraries organized

### Communications
- Set appropriate email frequency
- Test communications before sending
- Keep content relevant to group
- Respect member privacy

### Ministry Pages
- Choose visibility carefully
- Keep content current
- Use engaging images
- Update regularly

## Technical Details

### Custom Post Types
- **Groups**: `wrp_group`
- **Events**: `wrp_event`
- **Ministry Pages**: `wrp_ministry_page`

### Custom Capabilities
- `edit_wrp_group`, `edit_wrp_groups`
- `manage_wrp_events`, `edit_wrp_events`
- `manage_wrp_ministry_pages`
- `manage_group_members`
- `track_group_attendance`
- `manage_group_donations`
- `manage_group_finances`
- `manage_group_communications`
- `manage_group_documents`

### Data Storage
- Member data: Post meta `_wrp_group_members`
- Member info: Post meta `_wrp_member_info`
- Donations: Post meta `_wrp_group_donations`
- Expenses: Post meta `_wrp_group_expenses`
- Documents: Post meta `_wrp_shared_documents`, `_wrp_private_documents`
- Event data: Post meta `_wrp_event_*`
- Attendance: Post meta `_wrp_event_attendance`

## Troubleshooting

### Can't see Groups menu
- Ensure you have `manage_options` capability
- Check if user is Administrator
- Verify plugin is activated

### Members not appearing
- Ensure members are saved
- Check user exists in WordPress
- Refresh the page

### Events not showing
- Verify event is linked to correct group
- Check event is published
- Ensure group ID is set

### Documents not uploading
- Check file permissions
- Verify media library is accessible
- Check available disk space

### Financial totals incorrect
- Verify all transactions are saved
- Check for duplicate entries
- Recalculate by re-saving group

### Emails not sending
- Verify SMTP settings in WordPress
- Check member email addresses
- Test with single recipient first

## Future Enhancements

Planned features for future releases:
- Advanced reporting and analytics
- Recurring events
- Email templates and scheduling
- Member check-in system
- Mobile app integration
- Calendar integration
- Donation payment processing
- Export financial reports
- Advanced permission levels
- Custom fields for members
- Document version control
- Automated attendance reminders

## Support

For questions or issues:
1. Check this documentation
2. Review WordPress error logs
3. Verify user permissions
4. Test with default theme
5. Contact plugin support

---

**Version**: 1.0.0  
**Last Updated**: December 3, 2025

# Oxford Course Discovery System

A scalable and extensible Course Discovery system for WordPress, built without external plugins (except ACF).

## Features

- Custom post types for Courses, Instructors, and Providers
- Advanced filtering system with AND/OR logic
- Responsive, accessible frontend interface
- Custom database tables for optimized date queries
- Extensible filter architecture using Strategy Pattern
- AJAX-based filtering without page reload

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin
3. Install and activate Advanced Custom Fields PRO
4. Run the database setup:
   - The plugin will automatically create necessary tables on activation
5. Configure ACF fields (they're automatically registered)

## Setup Instructions

### 1. Post Types Creation
After activation, you'll see new menu items for:
- Courses
- Instructors
- Providers

### 2. Adding Content
1. **Add Providers**: Create provider entries with location information
2. **Add Instructors**: Create instructor profiles
3. **Add Courses**: 
   - Enter course details
   - Set price, descriptions
   - Select instructors and providers
   - Add start dates in Month-Year format

### 3. Displaying the Course Discovery
Use the shortcode `[course_discovery]` on any page or post.

## Database Schema

### Custom Tables
- `wp_oxford_course_dates`: Stores course start dates for optimized querying

### WordPress Native
- Uses standard WordPress posts, meta, and taxonomies
- Leverages WordPress query system with meta queries

## Filter Logic Architecture

### Design Patterns Used
- **Strategy Pattern**: Each filter type implements a common interface
- **Factory Pattern**: Filter manager creates and manages filter instances
- **Singleton Pattern**: Filter manager instance is globally available

### Filter Combination Logic
- **Between filters**: AND logic (must match all selected filter types)
- **Within filters**: OR logic (can match any value within a filter)

Example:
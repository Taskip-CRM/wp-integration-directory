# WP Integrations Directory

A comprehensive WordPress plugin that creates a professional integrations directory similar to Ghost's integrations page. Showcase your integrations with beautiful card layouts, advanced filtering, and detailed integration pages.

## Features

### Core Functionality
- **Custom Post Type**: Dedicated "Integration" post type with full WordPress features
- **Custom Taxonomy**: Hierarchical integration categories with icons and colors
- **Advanced Meta Fields**: Logo upload, pricing type, difficulty level, setup time, features list, and more
- **Professional Templates**: Ghost-inspired archive and single integration templates
- **Ajax Filtering**: Real-time search and filtering without page refreshes
- **Responsive Design**: Mobile-first approach with modern CSS Grid and Flexbox

### Admin Features
- **Rich Meta Boxes**: Comprehensive integration details with media uploaders
- **Custom Admin Columns**: Enhanced integration list with quick info
- **Bulk Edit Support**: Mass update integration types and categories
- **Quick Edit Fields**: Fast editing of key integration properties
- **Import/Export**: JSON-based data import and export functionality
- **Category Management**: Custom fields for category icons and colors
- **Admin Preview**: Preview integrations without leaving the admin

### Frontend Features
- **Beautiful Card Layout**: Ghost-inspired integration cards with hover effects
- **Advanced Search**: Real-time search with keyword highlighting
- **Category Filtering**: Filter by integration categories with counts
- **Type Filtering**: Filter by Free, Paid, or Freemium integrations
- **Load More**: Ajax-powered pagination with smooth animations
- **Lightbox Gallery**: Screenshot galleries with keyboard navigation
- **Social Sharing**: Built-in sharing buttons for social media
- **Bookmarking**: Client-side bookmarking with localStorage
- **Schema Markup**: SEO-optimized structured data
- **Breadcrumbs**: Clear navigation hierarchy

### Design & UX
- **Modern Design**: Clean, professional design inspired by Ghost
- **Dark Mode Support**: Automatic dark mode detection and styling
- **Accessibility**: WCAG 2.1 compliant with screen reader support
- **Performance**: Optimized CSS and JavaScript with lazy loading
- **Print Styles**: Print-friendly layouts
- **Reduced Motion**: Respects user motion preferences

## Installation

1. Download the plugin files
2. Upload to your `/wp-content/plugins/` directory
3. Activate the plugin through the WordPress admin
4. Navigate to "Integrations" in your admin menu

## File Structure

```
wp-integrations-directory/
├── wp-integrations-directory.php (Main plugin file)
├── includes/
│   ├── class-post-type.php        (Custom post type)
│   ├── class-taxonomy.php         (Custom taxonomy)
│   ├── class-meta-boxes.php       (Admin meta boxes)
│   ├── class-admin.php            (Admin functionality)
│   └── class-frontend.php         (Frontend functionality)
├── templates/
│   ├── archive-integration.php    (Archive template)
│   ├── single-integration.php     (Single template)
│   └── integration-card.php       (Card template)
├── assets/
│   ├── css/
│   │   ├── frontend.css           (Frontend styles)
│   │   └── admin.css              (Admin styles)
│   ├── js/
│   │   ├── frontend.js            (Frontend scripts)
│   │   └── admin.js               (Admin scripts)
│   └── images/
├── languages/                     (Translation files)
└── README.md
```

## Usage

### Creating Integrations

1. Go to **Integrations > Add New**
2. Fill in the integration title and description
3. Upload a logo (required)
4. Select integration categories
5. Set pricing type (Free/Paid/Freemium)
6. Add external website URL
7. Set difficulty level and setup time
8. Add key features list
9. Include setup requirements
10. Add code examples (optional)
11. Upload screenshots (optional)
12. Publish your integration

### Managing Categories

1. Go to **Integrations > Categories**
2. Add new categories or edit existing ones
3. Set category icons using Dashicons or Font Awesome classes
4. Choose category colors for visual distinction
5. Add descriptions for better SEO

### Display Options

#### Archive Page
- Accessible at `/integrations/`
- Shows all integrations in a grid layout
- Includes filtering and search functionality
- Responsive design for all devices

#### Single Integration Page
- Accessible at `/integration/integration-name/`
- Comprehensive integration details
- Related integrations section
- Social sharing capabilities

#### Shortcodes

**Display Integration Directory:**
```php
[integrations_directory category="analytics,email" type="free" limit="12" show_filters="yes"]
```

**Display Categories Grid:**
```php
[integration_categories show_count="yes" show_icons="yes"]
```

### Customization

#### Template Overrides
Copy templates from the plugin to your theme directory:
- `your-theme/wp-integrations-directory/archive-integration.php`
- `your-theme/wp-integrations-directory/single-integration.php`
- `your-theme/wp-integrations-directory/integration-card.php`

#### CSS Customization
The plugin uses CSS custom properties for easy customization:

```css
:root {
    --integration-primary-color: #6366f1;
    --integration-border-radius: 8px;
    --integration-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
```

#### Hooks and Filters
The plugin provides numerous hooks for customization:

```php
// Modify integration card output
add_filter('integration_card_html', 'custom_integration_card', 10, 2);

// Add custom meta fields
add_action('integration_meta_boxes', 'add_custom_meta_box');

// Customize archive query
add_filter('integration_archive_query_args', 'modify_archive_query');
```

## Settings

### Archive Settings
- **Archive Page Title**: Customize the main heading
- **Items Per Page**: Set number of integrations per page
- **Default Sort Order**: Choose default sorting method

### Import/Export
- **Export**: Download all integrations as JSON
- **Import**: Upload JSON file to import integrations

## Technical Requirements

- WordPress 5.0+
- PHP 7.4+
- Modern browser with ES6 support
- MySQL 5.6+ or MariaDB 10.1+

## Browser Support

- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **Optimized Queries**: Efficient database queries with proper indexing
- **Lazy Loading**: Images load on demand
- **Ajax Pagination**: Smooth content loading
- **Cached Queries**: WordPress object cache integration
- **Minified Assets**: Compressed CSS and JavaScript
- **CDN Ready**: All assets use relative URLs

## SEO Features

- **Schema Markup**: Rich snippets for integrations
- **Open Graph Tags**: Social media preview optimization
- **Meta Descriptions**: Automatic excerpt generation
- **Structured URLs**: SEO-friendly permalink structure
- **XML Sitemap**: Compatible with popular SEO plugins

## Security

- **Nonce Verification**: All forms use WordPress nonces
- **Data Sanitization**: Input sanitization and output escaping
- **Capability Checks**: Proper user permission validation
- **SQL Injection Prevention**: Prepared statements only
- **XSS Protection**: Escaped output throughout

## Multilingual Support

- **Translation Ready**: All strings use WordPress i18n functions
- **RTL Support**: Right-to-left language compatibility
- **WPML Compatible**: Works with WPML and Polylang
- **Included Languages**: English (default)

## Accessibility

- **WCAG 2.1 AA**: Compliant with accessibility guidelines
- **Screen Reader**: Proper ARIA labels and descriptions
- **Keyboard Navigation**: Full keyboard accessibility
- **Color Contrast**: Meets contrast ratio requirements
- **Focus Indicators**: Visible focus states

## Changelog

### Version 1.0.0
- Initial release
- Custom post type and taxonomy
- Archive and single templates
- Ajax filtering and search
- Admin interface with meta boxes
- Import/export functionality
- Responsive design
- Accessibility features

## Support

For support, feature requests, or bug reports:

1. Check the documentation first
2. Search existing issues
3. Create a new issue with detailed information
4. Include WordPress and PHP versions
5. Provide steps to reproduce any bugs

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Update documentation
6. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- Inspired by Ghost's integrations directory
- Icons from Dashicons and Font Awesome
- Design patterns from modern WordPress themes
- Accessibility guidelines from WebAIM

## Roadmap

### Planned Features
- Integration ratings and reviews
- Advanced analytics dashboard
- API endpoints for headless usage
- Integration request form
- Email notifications
- Comparison tool
- Advanced search filters
- Integration tagging system
- User favorites
- Integration statistics

### Performance Improvements
- Image optimization
- Database query optimization
- Caching enhancements
- CDN integration
- Critical CSS inlining

### Design Enhancements
- Additional layout options
- Theme color customization
- Animation preferences
- Typography controls
- Layout builder integration
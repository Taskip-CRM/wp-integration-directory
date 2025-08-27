# Step-by-Step Integration Guide Block

A powerful Gutenberg block that allows you to create interactive, visually appealing step-by-step guides for integrations, tutorials, and processes.

## Features

### ✨ **Rich Content Support**
- **Rich Text Editing**: Full WYSIWYG editing for titles and descriptions
- **Image Support**: Add screenshots and visual guides for each step
- **Code Blocks**: Syntax-highlighted code examples with copy functionality
- **Multiple Layouts**: Choose from numbered, timeline, or minimal layouts

### 🎨 **Customization Options**
- **Custom Accent Colors**: Match your brand colors
- **Layout Variations**: Three different visual styles
- **Responsive Design**: Looks great on all devices
- **Dark Mode Support**: Automatically adapts to user preferences

### 🔧 **Editor Features**
- **Drag & Drop**: Reorder steps with up/down controls
- **Duplicate Steps**: Copy existing steps to speed up creation
- **Live Preview**: See changes in real-time
- **Tabbed Interface**: Organized controls for images and code

### 🚀 **Frontend Functionality**
- **Interactive Navigation**: Next/Previous buttons with keyboard support
- **Progress Tracking**: Visual progress bar and step indicators
- **Auto-Advance**: Optional automatic progression through steps
- **Copy to Clipboard**: One-click code copying
- **Accessibility**: Full ARIA support and keyboard navigation

## Usage

### Adding the Block

1. In the WordPress editor, click the "+" button to add a new block
2. Search for "Step-by-Step Guide" or find it in the "Widgets" category
3. Click to add the block to your post or page

### Block Settings

#### **Guide Settings**
- **Guide Title**: Main heading for your tutorial
- **Guide Description**: Brief overview of what users will learn
- **Layout Style**: Choose between:
  - **Numbered Steps**: Traditional numbered list format
  - **Timeline**: Vertical timeline with connected steps
  - **Minimal**: Clean, minimal design without heavy styling
- **Show Step Numbers**: Toggle step numbers on/off
- **Accent Color**: Customize the primary color used throughout the guide

#### **Step Management**
- **Add Steps**: Use the "Add New Step" button in settings or at the bottom
- **Reorder Steps**: Use up/down arrows in the editor
- **Duplicate Steps**: Copy existing steps with the "Copy" button
- **Remove Steps**: Delete steps with the "×" button

### Creating Steps

Each step can contain:

#### **Basic Content**
- **Step Title**: Descriptive heading for the step
- **Step Description**: Detailed instructions using rich text editor

#### **Visual Content (Image Tab)**
- **Add Images**: Upload screenshots, diagrams, or illustrations
- **Image Alt Text**: Automatically populated from media library
- **Remove Images**: Easy removal if no longer needed

#### **Code Content (Code Tab)**
- **Show Code Block**: Toggle to enable code display
- **Code Language**: Choose from HTML, CSS, JavaScript, PHP, JSON, XML
- **Code Content**: Add your code examples

## Frontend Experience

### Navigation
- **Progress Bar**: Shows completion percentage
- **Step Counter**: Current step out of total steps
- **Navigation Buttons**: Previous/Next with disabled states
- **Keyboard Support**: Arrow keys, Home, End for navigation

### Interactive Features
- **Auto-Activation**: Steps become active as user scrolls
- **Click Navigation**: Click any step to jump to it
- **Code Copying**: Click copy button to copy code to clipboard
- **Responsive**: Adapts layout for mobile devices

### Accessibility
- **ARIA Labels**: Full screen reader support
- **Keyboard Navigation**: Complete keyboard accessibility
- **High Contrast**: Supports high contrast mode
- **Reduced Motion**: Respects user motion preferences

## Styling and Customization

### CSS Custom Properties
The block uses CSS custom properties for easy customization:

```css
.wp-block-step-by-step-guide {
    --accent-color: #0073aa;
    --step-bg: #ffffff;
    --step-border: #e5e7eb;
    --text-color: #374151;
    --text-light: #6b7280;
}
```

### Layout Variations

#### Numbered Steps (Default)
- Clean numbered circles
- Clear step progression
- Professional appearance

#### Timeline
- Connected vertical timeline
- Perfect for process flows
- Visual step relationships

#### Minimal
- Subtle step indicators
- Focus on content
- Clean, unobtrusive design

## Advanced Features

### Auto-Advance
Enable automatic progression through steps:

```javascript
const guide = new StepGuide($('.wp-block-step-by-step-guide'));
const autoAdvance = new StepGuideAutoAdvance(guide, 10000); // 10 second interval
```

### Analytics Integration
Track user engagement:

```javascript
const analytics = new StepGuideAnalytics(guide);
// Automatically tracks step views and completion rates
```

### Custom Events
Listen for step changes:

```javascript
$('.wp-block-step-by-step-guide').on('stepChanged', function(e, stepIndex) {
    console.log('User is now on step:', stepIndex);
});
```

## Browser Support

- **Modern Browsers**: Chrome 60+, Firefox 60+, Safari 12+, Edge 79+
- **Mobile**: iOS Safari 12+, Android Chrome 60+
- **Accessibility**: NVDA, JAWS, VoiceOver compatible

## Performance

- **Lazy Loading**: Images load as needed
- **Minimal JavaScript**: Only loads when block is present
- **CSS Grid**: Modern layout techniques
- **Optimized**: Compressed assets and efficient code

## Best Practices

### Content Guidelines
1. **Keep Steps Focused**: One main action per step
2. **Use Clear Titles**: Descriptive, action-oriented headings
3. **Add Visuals**: Screenshots help clarify instructions
4. **Include Code**: Provide copy-paste ready examples
5. **Test Flow**: Ensure logical progression

### Design Tips
1. **Choose Appropriate Layout**: Timeline for processes, numbered for tutorials
2. **Consistent Images**: Similar dimensions and style
3. **Readable Code**: Use proper indentation and comments
4. **Color Accessibility**: Ensure sufficient contrast
5. **Mobile First**: Test on smaller screens

### Performance Tips
1. **Optimize Images**: Compress screenshots before upload
2. **Limit Steps**: Keep to 10-15 steps maximum
3. **Minimize Code**: Only include necessary code examples
4. **Test Loading**: Check performance on slower connections

## Troubleshooting

### Common Issues

#### Block Not Appearing
- Ensure plugin is activated
- Check if Gutenberg is enabled
- Verify WordPress version (5.0+)

#### Styles Not Loading
- Clear caching plugins
- Check theme compatibility
- Verify file permissions

#### JavaScript Errors
- Check browser console
- Ensure jQuery is loaded
- Verify no plugin conflicts

### Support Resources
- Documentation: Plugin admin area
- Community: WordPress support forums
- Updates: Automatic via WordPress

## Examples

### Basic Tutorial
Perfect for software tutorials, how-to guides, and instructional content.

### Integration Guide
Ideal for API integrations, third-party service setups, and technical implementations.

### Process Documentation
Great for workflow documentation, procedures, and step-by-step processes.

## Version History

### 1.0.0
- Initial release
- Core functionality
- Three layout options
- Full accessibility support
- Mobile responsive design
# Edgering Markdown

Enhanced Markdown parser with custom extensions and improvements.
Forked from [Michelf/php-markdown](https://github.com/Michelf/php-markdown)

> **Important:** Edgering extensions translate simplified input shortcuts into standard Markdown syntax. They don't create new markdown codes, but provide easier ways to write existing Markdown features.

## Features

- **Input Shortcuts**: Translates simplified syntax into standard Markdown Extra format
- **PHP Version Compatibility**: Automatically selects appropriate Markdown library based on PHP version
- **Enhanced Images**: Support for nested images and alignment shortcuts
- **Custom Tables**: Simplified table syntax that converts to standard Markdown tables
- **Smart Links**: Automatic CSS class assignment for social media and email links
- **Backward Compatibility**: Maintains full compatibility with standard Markdown Extra

## Installation & Usage

### Basic Usage (Recommended)

```php
require_once 'markdown.php';
echo Markdown($text);
```

This will automatically:
- Check PHP version compatibility (PHP 7.4+ uses enhanced version)
- Load appropriate Markdown library
- Apply all Edgering enhancements

### Direct Usage

For enhanced features only:
```php
require_once 'Edgering/MarkdownEdgering.php';
$text = \Michelf\MarkdownEdgering::defaultTransform($text);
$text = \Michelf\MarkdownExtra::defaultTransform($text);
$text = \Michelf\MarkdownEdgering::defaultPostTransform($text);
```

For standard Markdown Extra without enhancements:
```php
require_once 'Michelf/MarkdownExtra.inc.php';
echo \Michelf\MarkdownExtra::defaultTransform($text);
```



## Enhancements

> All Edgering enhancements work as **input translators** - they convert simplified shortcuts into standard Markdown Extra syntax before processing.

### 1. Nested Images (`doNestedImages`)

Converts simplified image-link syntax to standard Markdown nested images:

**Input Shortcut:**
```markdown
[image.jpg](link-url)
```

**Translated to Standard Markdown:**
```markdown
[![](image.jpg)](link-url)
```

### 2. Image Alignment (`doImageAligns`)

Converts alignment shortcuts to standard Markdown Extra attribute syntax:

**Input Shortcuts:**
```markdown
![Alt text](image.jpg)[c]  # Center
![Alt text](image.jpg)[l]  # Left
![Alt text](image.jpg)[r]  # Right
![Alt text](image.jpg)[p]  # Right (alias)
```

**Translated to Standard Markdown Extra:**
```markdown
![Alt text](image.jpg){.ImgAlignCenter}
![Alt text](image.jpg){.ImgAlignLeft}
![Alt text](image.jpg){.ImgAlignRight}
```

### 3. Simplified Tables (`doTranslateTables`)

Converts bracket-and-semicolon syntax to standard Markdown tables:

**Input Shortcut:**
```markdown
[Header 1;Header 2;Header 3
Value 1;Value 2;Value 3
Value 4;Value 5;Value 6]
```

**Translated to Standard Markdown:**
```markdown
| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Value 1  | Value 2  | Value 3  |
| Value 4  | Value 5  | Value 6  |
```

**With explicit header separator:**
```markdown
[Header 1;Header 2;Header 3
----;----;----
Value 1;Value 2;Value 3]
```

### 4. Smart Link Classes (`doKnownLinks`)

Automatically adds CSS classes to recognized platform links using standard Markdown Extra attribute syntax:

**Default Supported Platforms:**
- Facebook → `.lnkFB`
- Instagram → `.lnkIG`
- Twitter → `.lnkTW`
- YouTube → `.lnkYT`
- Email addresses → `.lnkMail`

**Examples:**
```markdown
[Visit us](https://facebook.com/page) → [Visit us](https://facebook.com/page){.lnkFB}
<user@example.com> → [user@example.com](mailto:user@example.com){.lnkMail}
```

**add lnkPDF class to PDF links:**
```markdown
[Document](file.pdf) → [Document](file.pdf){.lnkPDF}
```

## Managing Known Links

You can customize which platforms are recognized and their CSS classes:

### Adding New Platforms

```php
// Add LinkedIn support
\Michelf\MarkdownEdgering::addKnownLink('linkedin', 'LI');

// Add GitHub support  
\Michelf\MarkdownEdgering::addKnownLink('github', 'GH');

// Add TikTok support
\Michelf\MarkdownEdgering::addKnownLink('tiktok', 'TT');
```

### Removing Platforms

```php
// Remove Twitter support
\Michelf\MarkdownEdgering::removeKnownLink('twitter');
```

### Getting All Known Links

```php
// Get current configuration
$links = \Michelf\MarkdownEdgering::getKnownLinks();
print_r($links);
```

### Direct Property Access

```php
// Direct access to the static property
\Michelf\MarkdownEdgering::$known_links['custom'] = 'CU';
unset(\Michelf\MarkdownEdgering::$known_links['facebook']);
```

## Requirements

- **PHP 7.4+**: For enhanced features and modern syntax
- **PHP < 7.4**: Falls back to legacy `Michelf/previous/markdown-extra-1.2.8.php`

## File Structure

```
markdown/
├── markdown.php                         # Main entry point with auto-detection
├── Michelf/                             # Original Michelf Markdown library
│   ├── Markdown.php
│   ├── MarkdownExtra.php
│   ├── previous/
│   │   └── markdown-extra-1.2.8.php    # Legacy version for PHP < 7.4
│   └── ...
└── Edgering/                           # Edgering enhancements
    └── MarkdownEdgering.php
```

## License

This project maintains the same license as the original Michelf/php-markdown project.
        

# Edgering Markdown

Wrapper for Markdown Extra with Edgering enhancements.
Forked from [Michelf/Markdown](https://github.com/Michelf/php-markdown)

Function Markdown($text) is available globally and will be set due compatibility.


## doNestedImages($text)

Allow shortcodes inside images
      
    [img url](href) -> [![](url)](href)

## doImageAligns($text)

    ![img](url)[A-Z] -> ![img](url){.class}

- "c" => ImgAlignCenter
- "l" => ImgAlignLeft
- "p" => ImgAlignRight
- "r" => ImgAlignRight

## doTranslateTables($text)

Allows insert table this way:

    [table; cell; cell
    cell; cell; cell]

## doKnownLinks($text)

Allows to add class to links in markdown:

    [link text](known-link){lnk"class"}

   // Add new platforms
   
    \Michelf\MarkdownEdgering::addKnownLink('linkedin', 'LI');
    \Michelf\MarkdownEdgering::addKnownLink('github', 'GH');
    \Michelf\MarkdownEdgering::addKnownLink('tiktok', 'TT');

    // Remove a platform

    \Michelf\MarkdownEdgering::removeKnownLink('twitter');

    // Get all known links
   
    $links = \Michelf\MarkdownEdgering::getKnownLinks();
        

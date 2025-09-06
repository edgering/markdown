<?php

namespace Michelf;

/**
 * Markdown Extra Parser Class
 */

class MarkdownEdgering extends \Michelf\Markdown
{
    public static array $table_params = array();

    public static array $known_links = array(
        'facebook' => 'FB',
        'instagram' => 'IG',
        'twitter' => 'TW',
        'youtube' => 'YT',
    );

    public function __construct() {}

    /**
     * Add a new known link platform
     * 
     * @param string $platform The platform name (e.g., 'linkedin', 'github')
     * @param string $class The CSS class abbreviation (e.g., 'LI', 'GH')
     * @return void
     */

    public static function addKnownLink(string $platform, string $class): void
    {
        self::$known_links[strtolower($platform)] = $class;
    }

    /**
     * Remove a known link platform
     * 
     * @param string $platform The platform name to remove
     * @return void
     */

    public static function removeKnownLink(string $platform): void
    {
        unset(self::$known_links[strtolower($platform)]);
    }

    /**
     * Get all known links
     * 
     * @return array The array of known links
     */

    public static function getKnownLinks(): array
    {
        return self::$known_links;
    }

    public static function defaultTransform(string $text): string
    {
        $text = self::doNestedImages($text);
        $text = self::doTranslateTables($text);
        $text = self::doKnownLinks($text);
        $text = self::doImageAligns($text);

        return $text;
    }

    public static function defaultPostTransform(string $text): string
    {
        $text = self::replaceTableHtml($text);
        $text = self::replaceImgHtml($text);

        return $text;
    }

    /** 
     * Add class to known common link
     * 
     * [facebook|isntagram|mailto|twitter|youtube](href) 
     * 
     * -> [facebook|isntagram|mailto|twitter|youtube](href){.class} 
     * 
     * Add a new platform
     * 
     *   \Michelf\MarkdownEdgering::$known_links['linkedin'] = 'LI';
     *
     * Modify an existing platform
     *      
     *   \Michelf\MarkdownEdgering::$known_links['facebook'] = 'Facebook';
     *
     * Remove a platform
     * 
     *   unset(\Michelf\MarkdownEdgering::$known_links['twitter']);
     * 
     *  + also handle <email@address> to [email@address](mailto:email@address){.lnkMail}  
     *  + also add .pdf links to {.lnkPDF}
     * 
     */

    public static function doKnownLinks($text): string
    {
        $text = self::doKnownLinksEmail($text);
        
        // Transform short url link version to default
        // <{ref}> to [{ref}]({ref})

        $text = preg_replace_callback('/<([^>]+)>/', function ($matches) {
            $url = $matches[1];
            // Přidáno: kontrola, zda začíná na http
            if (strpos($url, 'http') === 0) {
                $lnk = str_replace('https://', '', $url);
                return "[{$lnk}]({$url})";
            }
            // Pokud nezačíná na http, vrať původní tag
            return "<{$url}>";
        }, $text);

        
        // Create pattern to match any of the known platform names in URLs
        $platforms = implode('|', array_keys(self::$known_links));

        // Pattern to match markdown links with known social media platforms
        // Matches: [text](https://www.facebook.com/...) or [text](https://instagram.com/...)
        $pattern = "/(\[[^\]]+\])\((https?:\/\/)?(www\.)?({$platforms})\.com[^\)]*\)(?!\{[^}]*\})/i";

        $text = preg_replace_callback($pattern, function ($matches) {
            $link_text = $matches[1];               // [text]
            $platform = strtolower($matches[4]);    // platform name
            $full_match = $matches[0];

            // Extract the full URL from the original match
            preg_match('/\(([^)]+)\)/', $full_match, $url_matches);
            $full_url = $url_matches[1];

            // Get the class abbreviation for this platform
            $class = self::$known_links[$platform] ?? ucfirst($platform);

            // Return the link with added class
            return "{$link_text}({$full_url}){.lnk{$class}}";
        }, $text);

        // -- find .pdf and add class lnkPDF
        
        $text = preg_replace_callback('/(\[[^\]]+\])\(([^)]+\.pdf)(?!\{[^}]*\})\)/i', function ($matches) {
            $link_text = $matches[1];               // [text]
            $full_url = $matches[2];                // full URL ending with .pdf
            return "{$link_text}({$full_url}){.lnkPDF}";
        }, $text);

        return $text;
    }

    public static function doKnownLinksEmail($text): string
    {
        // Handle mailto links separately
        // change: <email@address> to [email@address](mailto:email@address){.lnkMail}
        // ! force search for @ to match only email addresses

        $text = preg_replace_callback('/<([^>]+@[^>]+\.[a-zA-Z]{2,})>/', function ($matches) {
            $email = $matches[1];
            return "[{$email}](mailto:{$email}){.lnkMail}";
        }, $text);

        return $text;
    }

    /**
     *  Allow shortcodes inside images
     * 
     *  [img url](href) -> [![](url)](href)
     * 
     */

    public static function doNestedImages($text): string
    {
        $regex = '/\[([^\]]+\.[a-z]{3,4})\]\(([^)]*)\)/is';

        if (preg_match_all($regex, $text, $matches)) {

            foreach ($matches[1] as $match) {
                $text = str_replace("[{$match}]", "[![]({$match})]", $text);
            }
        }

        return $text;
    }


    /**
     * Translate old way of aligning to MarkdownExtra
     * 
     *  ![img](url)[A-Z] -> ![img](url){.class}
     * 
     */

    public static function doImageAligns($text): string
    {
        $regex = '/(\!\[[^\]]*\]\([^\)]*\))\[([A-Za-z])\]/';

        $class_list = array(
            "c" => "ImgAlignCenter",
            "l" => "ImgAlignLeft",
            "p" => "ImgAlignRight",
            "r" => "ImgAlignRight",
        );

        $copy = $text; // keep a copy of the original text

        if (preg_match_all($regex, $text, $matches)) {

            foreach ($matches[2] as $i => $align) {
                $align = strtolower($align);
                if (isset($class_list[$align])) {
                    $class = $class_list[$align];
                } else {
                    $class = "ImgAlign{$align}";
                }

                $result = sprintf("%s{.%s}", $matches[1][$i], $class);

                $copy = str_replace($matches[0][$i], $result, $copy);
            }

            // file_put_contents("log/text.txt", htmlspecialchars($copy));
        }

        return $copy;
    }

    public static function doTranslateTables($text): string
    {
        if (stripos($text, '[') === TRUE) {
            return $text;
        }

        $buffer = array();
        $opening = NULL;
        $table_count = 0;

        $lines = explode("\n", $text);

        foreach ($lines as $i => $line) {

            $buffer[] = $line;

            if (empty($line)) {
                continue; // skip empty lines or lines not starting with [
            }

            $l = trim($line);

            if (empty($l)) {
                continue; // skip empty lines
            }

            if ($l[0] === '[' && stripos($line, ']') === FALSE) {
                $opening = $i;
            }

            // test if last line is ending with ]

            if (substr($l, -1) === ']' && $opening !== NULL) {

                $buffer[$i] = substr($l, 0, -1);
                $buffer[$opening] = substr($buffer[$opening], 1);

                // -- set this->table_params value as true

                self::$table_params[] = array(
                    'opening' => $opening,
                    'closing' => $i,
                );

                for ($j = $opening; $j <= $i; $j++) {

                    if (strpos($buffer[$j], '|') === FALSE) {
                        $buffer[$j] = str_replace(';', '|', $buffer[$j]);
                    }

                    if ($j == $opening) {

                        $d = trim($buffer[$j + 1], "|");

                        $TableHeaderInUse = $d[0] == '-';

                        // -- test if table header is defined

                        self::$table_params[$table_count] = array(
                            "use_header" => $TableHeaderInUse,
                        );

                        if (!$TableHeaderInUse) {

                            $l = trim($buffer[$j], '|');
                            $c = substr_count($l, '|');
                            $d = str_repeat('-------- | ', $c) . "--------";

                            if ($buffer[$j][0] == '|') {
                                $d = "| {$d} |";
                            }

                            $buffer[$j] .= "\n{$d}";
                        }
                    }
                }

                $opening = NULL; // reset opening            
                $table_count++; // increment table count
            }
        }

        // file_put_contents("log/table.txt", implode("\n", $buffer));

        $text = implode("\n", $buffer);

        return $text;
    }

    /**
     *  Replace HTML tables with custom classes
     *  
     *  - This function will replace all <table> tags with a class "tbl_MD"
     *  - If the table has a header, it will keep it as is
     *  - If the table does not have a header, it will replace <th> with <td>
     *  - It will also replace the first <td> in each row with <th> if the table does not have a header
     */

    public static function replaceTableHtml($text): string
    {
        if (!count(self::$table_params)) {
            return $text; // no tables to replace
        }

        // -- match all tables 

        $pattern = '/<table[^>]*>(.*?)<\/table>/is';

        preg_match_all($pattern, $text, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $i => $table) {

                $result = str_replace('<table', '<table class="tbl_MD"', $table);

                // -- check if table should use header
                if (isset(self::$table_params[$i]) && self::$table_params[$i]['use_header']) {
                    // --- do nothing                        
                } else {
                    // -- replace table without header
                    // -- replace th inside THEAD with td                    

                    $result = str_replace('<thead>', '', $result);
                    $result = str_replace('</thead>', '', $result);
                    $result = str_replace('<th>', '<td>', $result);
                    $result = str_replace('</th>', '</td>', $result);

                    // -- match first occurence of td and replace with th
                    // -- in each row!

                    $result = preg_replace_callback('/<tr>(.*?)<\/tr>/is', function ($matches) {
                        return preg_replace('/<td>/', '<th>', $matches[0], 1) . '</th>';
                    }, $result);
                }

                $text = str_replace($table, $result, $text);
            }
        }

        return $text;
    }

    /**
     *  Try to add width and height attributes
     *  to <img> tags
     * 
     *  - by checking if the file exists
     *  - and using getimagesize() to get the dimensions  
     */

    public static function replaceImgHtml($text): string
    {
        $pattern = '/<img[^>]+src="([^"]+)"[^>]*>/i';
        $text = preg_replace_callback($pattern, function ($matches) {
            $src = $matches[1];

            if (empty($src)) {
                return $matches[0]; // return original img tag if src is empty
            }

            if ($src[0] !== '/') {
                $src = '/' . $src;
            }

            $filePath = $_SERVER['DOCUMENT_ROOT'] . $src;

            if (file_exists($filePath)) {
                $imageSize = getimagesize($filePath);
                if ($imageSize) {
                    $attr = $imageSize[3];
                    // replace <img with <img {attr}
                    return str_replace('<img', "<img {$attr}", $matches[0]);
                }
            }

            return $matches[0];
        }, $text);
        return $text;
    }
}

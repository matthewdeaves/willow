<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * ContentSanitizer Utility
 *
 * Provides HTML sanitization to prevent XSS attacks while preserving
 * safe HTML formatting for CMS content (articles, pages, etc.).
 *
 * Security features:
 * - Removes <script> tags and their content
 * - Removes event handler attributes (onclick, onerror, onload, etc.)
 * - Removes javascript:, vbscript:, and data: URLs from href/src attributes
 * - Preserves safe HTML tags for content formatting
 */
class ContentSanitizer
{
    /**
     * Event handler attributes to remove (XSS vectors)
     *
     * @var array<string>
     */
    private static array $dangerousAttributes = [
        'onabort', 'onafterprint', 'onbeforeprint', 'onbeforeunload', 'onblur',
        'oncanplay', 'oncanplaythrough', 'onchange', 'onclick', 'oncontextmenu',
        'oncopy', 'oncuechange', 'oncut', 'ondblclick', 'ondrag', 'ondragend',
        'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop',
        'ondurationchange', 'onemptied', 'onended', 'onerror', 'onfocus',
        'onhashchange', 'oninput', 'oninvalid', 'onkeydown', 'onkeypress',
        'onkeyup', 'onload', 'onloadeddata', 'onloadedmetadata', 'onloadstart',
        'onmessage', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover',
        'onmouseup', 'onmousewheel', 'onoffline', 'ononline', 'onpagehide',
        'onpageshow', 'onpaste', 'onpause', 'onplay', 'onplaying', 'onpopstate',
        'onprogress', 'onratechange', 'onreset', 'onresize', 'onscroll',
        'onsearch', 'onseeked', 'onseeking', 'onselect', 'onstalled', 'onstorage',
        'onsubmit', 'onsuspend', 'ontimeupdate', 'ontoggle', 'onunload',
        'onvolumechange', 'onwaiting', 'onwheel', 'onanimationend',
        'onanimationiteration', 'onanimationstart', 'ontransitionend',
        'onpointerdown', 'onpointerup', 'onpointermove', 'onpointerenter',
        'onpointerleave', 'onpointerover', 'onpointerout', 'onpointercancel',
        'ongotpointercapture', 'onlostpointercapture',
    ];

    /**
     * Sanitize HTML content to prevent XSS attacks
     *
     * @param string|null $html The HTML content to sanitize
     * @return string The sanitized HTML content
     */
    public static function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Remove script tags and their content
        $html = self::removeScriptTags($html);

        // Remove dangerous event handler attributes
        $html = self::removeEventHandlers($html);

        // Remove javascript:, vbscript:, data: URLs
        $html = self::removeDangerousUrls($html);

        // Remove <object>, <embed>, <applet> tags (Flash/plugin vectors)
        $html = self::removePluginTags($html);

        // Remove <meta>, <link>, <base> tags that could redirect or inject
        $html = self::removeMetaTags($html);

        // Remove style attributes containing expressions or javascript
        $html = self::sanitizeStyleAttributes($html);

        return $html;
    }

    /**
     * Remove script tags and their content
     *
     * @param string $html The HTML content
     * @return string HTML with script tags removed
     */
    private static function removeScriptTags(string $html): string
    {
        // Remove <script>...</script> including content
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);

        // Remove self-closing script tags
        $html = preg_replace('/<script\b[^>]*\/>/is', '', $html);

        // Remove orphaned opening script tags
        $html = preg_replace('/<script\b[^>]*>/is', '', $html);

        return $html ?? '';
    }

    /**
     * Remove event handler attributes
     *
     * @param string $html The HTML content
     * @return string HTML with event handlers removed
     */
    private static function removeEventHandlers(string $html): string
    {
        foreach (self::$dangerousAttributes as $attr) {
            // Match the attribute with various quote styles and whitespace
            $pattern = '/\s+' . preg_quote($attr, '/') . '\s*=\s*(["\'])[^"\']*\1/is';
            $html = preg_replace($pattern, '', $html);

            // Handle unquoted attribute values
            $pattern = '/\s+' . preg_quote($attr, '/') . '\s*=\s*[^\s>]+/is';
            $html = preg_replace($pattern, '', $html);
        }

        return $html ?? '';
    }

    /**
     * Remove dangerous URL schemes from href and src attributes
     *
     * @param string $html The HTML content
     * @return string HTML with dangerous URLs removed
     */
    private static function removeDangerousUrls(string $html): string
    {
        // Remove javascript: URLs from common attributes
        $pattern = '/\b(href|src|action|formaction|poster|data)\s*=\s*(["\'])?\s*javascript\s*:[^"\'>\s]*/is';
        $html = preg_replace($pattern, '$1=$2#blocked', $html);

        // Remove vbscript: URLs from common attributes
        $pattern = '/\b(href|src|action|formaction|poster|data)\s*=\s*(["\'])?\s*vbscript\s*:[^"\'>\s]*/is';
        $html = preg_replace($pattern, '$1=$2#blocked', $html);

        // Remove data: URLs (except for safe image types)
        $html = preg_replace_callback(
            '/\b(src)\s*=\s*(["\'])\s*data:(?!image\/(png|gif|jpeg|webp);base64,)[^"\'>\s]*/is',
            function ($matches) {
                return $matches[1] . '=' . $matches[2] . '#blocked';
            },
            $html,
        );

        return $html ?? '';
    }

    /**
     * Remove plugin embedding tags
     *
     * @param string $html The HTML content
     * @return string HTML with plugin tags removed
     */
    private static function removePluginTags(string $html): string
    {
        // Remove <object> tags
        $html = preg_replace('/<object\b[^>]*>.*?<\/object>/is', '', $html);

        // Remove <embed> tags
        $html = preg_replace('/<embed\b[^>]*\/?>/is', '', $html);

        // Remove <applet> tags
        $html = preg_replace('/<applet\b[^>]*>.*?<\/applet>/is', '', $html);

        return $html ?? '';
    }

    /**
     * Remove meta, link, and base tags
     *
     * @param string $html The HTML content
     * @return string HTML with meta tags removed
     */
    private static function removeMetaTags(string $html): string
    {
        $html = preg_replace('/<meta\b[^>]*\/?>/is', '', $html);
        $html = preg_replace('/<link\b[^>]*\/?>/is', '', $html);
        $html = preg_replace('/<base\b[^>]*\/?>/is', '', $html);

        return $html ?? '';
    }

    /**
     * Sanitize style attributes to remove expression() and javascript
     *
     * @param string $html The HTML content
     * @return string HTML with sanitized style attributes
     */
    private static function sanitizeStyleAttributes(string $html): string
    {
        // Remove style attributes containing expression()
        $html = preg_replace('/\bstyle\s*=\s*(["\'])[^"\']*expression\s*\([^"\']*\1/is', '', $html);

        // Remove style attributes containing javascript
        $html = preg_replace('/\bstyle\s*=\s*(["\'])[^"\']*javascript\s*:[^"\']*\1/is', '', $html);

        // Remove style attributes containing url() with javascript
        $html = preg_replace('/\bstyle\s*=\s*(["\'])[^"\']*url\s*\(\s*["\']?\s*javascript:[^"\']*\1/is', '', $html);

        return $html ?? '';
    }
}

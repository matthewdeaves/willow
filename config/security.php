<?php
// config/security.php
return [
    'IpSecurity' => [
        'suspiciousPatterns' => [
            // Path Traversal & LFI Attempts
            '/\.\.[\/\\\\]/',
            '/%2e%2e[\/\\\\]/',
            '/%252e%252e\//',
            '/%c0%ae%c0%ae\//',

            // System Files
            '/\/etc\/passwd/',
            '/\/etc\/shadow/',
            '/boot\.ini/',
            '/\.env$/',
            '/\.git\//',
            '/\.htaccess$/',
            '/web\.config$/',
            '/composer\.json$/',

            // Shell Detection
            '/shell\.php$/',
            '/cmd\.php$/',
            '/eval\(/',
            '/system\(/',
            '/exec\(/',
            '/shell_exec\(/',

            // SQL Injection
            '/union.*select/i',
            '/select.*from/i',
            '/insert.*into/i',
            '/update.*set/i',
            '/delete.*from/i',
            '/drop.*table/i',
            '/--.*$/',
            '/\/\*.*\*\//',

            // File Extensions (Originals)
            '/\.(php|phtml|php3|php4|php5|phps)$/i', 
            '/\.asp$|\.aspx$|\.jsp$|\.jspx$/i',
            '/\.(exe|dll|cgi|pl)$/i', 

            // XSS (Originals)
            '/<script/i', 
            '/<iframe/i', 
            '/javascript:/i', 
            '/onerror=/i', 
            '/onload=/i', 

            // Common Probes (Originals)
            '/phpinfo\.php/i', 
            '/wp-admin/i', 
            '/wp-content/i', 
            '/phpmyadmin/i', 
            '/mysql/i', 

            // Sensitive Paths (Originals)
            '/\/proc\/self/i', 
            '/\/proc\/[0-9]+/i', 
            '/\/tmp\//i', 
            '/\/var\/tmp\//i', 

            // Command Injection (Originals)
            '/;&|wget/i', 
            '/;&|curl/i', 
            '/;&|bash/i', 
            '/;&|sh/i', 
            '/;&|nc /i', 

            // Protocol handlers (Originals)
            '/php:\/\//i', 
            '/file:\/\//i', 
            '/data:\/\//i', 

            // Backup files (Originals)
            '/\.bak$/i', 
            '/\.old$/i', 
            '/\.backup$/i', 
            '/\.sql$/i', 

            // Arbitrary File Upload Attempts / Malicious Extensions - Removed .tar.gz
            '/\.phar$/i',
            '/\.(exe|dll|cgi|pl|bat|sh|ps1|vbs|aspx|jsp|jspx)$/i',
            '/\.zip(\.php)?$/i',
            '/\b(upload|remote|shell|backdoor)\.(php|jsp|asp|pl)/i',

            // LFI/RFI Variants
            '/php:\/\//i',
            '/data:\/\/text\/plain;base64/i',
            '/\binclude\b/i',
            '/\brequire\b/i',
            '/\bfile_get_contents\b/i',
            '/\bexec_shell\b/i',
            '/\/proc\/self\/environ/i',
            '/\/var\/log\//i',
            '/\.\/\.git/i',

            // SQL Injection (More variants)
            '/\bOR\s+\d+=\d+\b/i',
            '/\bCAST\b/i',
            '/\bCONCAT\b/i',
            '/\bINFORMATION_SCHEMA\b/i',
            '/[\dA-F]{32}/i', 
            '/\bSLEEP\(\d+\)/i',
            '/\bBENCHMARK\(\d+,\s*[\w.-]+\)/i',

            // Command Injection (More variants)
            '/\|\||&&|%0A|%0D/i',
            '/\$\{cmd\}/i',
            '/\bwhoami\b|\bid\b|\bcat\b/i',
            '/\bchmod\b|\brm\b|\bmkdir\b/i',
            '/dl\(\)|passthru\(/i',

            // XSS (More subtle/obfuscated)
            '/%3Cxss%3E/i',
            '/<img\b[^>]*src\s*=\s*"[^"]*"\b/i',
            '/<body\b[^>]*onload/i',
            '/<svg\b[^>]*onload/i',
            '/\bonfocus=.*?\b/i',
            '/\bstyle=.*?\bexpression/i',
            '/\balert\(/i',
            '/\bprompt\(/i',
            '/\bconfirm\(/i',
            '/\bdocument\.cookie/i',
            '/\bwindow\.location/i',
            '/\bString.fromCharCode/i',

            // SSRF / Port Scanning Probes
            '/\b(localhost|127\.0\.0\.1|192\.168\.\d{1,3}\.\d{1,3}|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|172\.(1[6-9]|2[0-9]|3[0-1])\.\d{1,3}\.\d{1,3})\b/i',
            '/:([1-9][0-9]{1,4})\b/i',
            '/\bfile:\/\/\//i',
            '/\bgopher:\/\//i',
            '/\bcgi-bin/i',
            '/test\.php|debug\.php/i',

            // Specific Probes / Known Vulnerabilities
            '/php\.ini/i',
            '/conf\.d/i',
            '/robots\.txt.*(\.\.|%2e%2e)/i',
            '/sitemap\.xml.*(\.\.|%2e%2e)/i',
            '/~root/i',
            '/\b(dokuwiki|joomla|drupal|magento|vbulletin|cpanel)\b/i',
            '/\b(?:\w+\.)?(bak|old|backup|sql|rar|7z|conf|ini)\b$/i',
        ],
    ],
];
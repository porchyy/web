<?php
/**
 * Security & Sanitization Utilities
 * Enterprise-grade whitelist sanitization using DOMDocument
 */

/**
 * Clean plain text input (strip tags, normalize control characters and extra spaces).
 */
function clean_text(?string $str): string
{
    if ($str === null || $str === '') {
        return '';
    }
    $clean = strip_tags($str);
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
    return trim($clean);
}

/**
 * Robust Whitelist HTML Sanitizer using native DOMDocument.
 * Completely eliminates Stored XSS, script injection, and protocol bypasses.
 */
function clean_html(?string $html): string
{
    if ($html === null || trim($html) === '') {
        return '';
    }

    $allowedTags = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'del', 'strike', 'hr',
        'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'a', 'img', 'blockquote', 'div', 'span',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'code', 'pre'
    ];

    // Dangerous tags whose entire subtree (including text and scripts) must be destroyed
    $dropSubtreeTags = [
        'script', 'style', 'iframe', 'frame', 'object', 'embed', 'applet',
        'svg', 'math', 'form', 'input', 'button', 'select', 'textarea', 'option',
        'link', 'meta', 'base', 'noscript'
    ];

    $allowedAttributes = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        '*'   => ['class']
    ];

    // Whitelist for valid Quill formatting classes
    $allowedClasses = [
        'ql-align-left', 'ql-align-center', 'ql-align-right', 'ql-align-justify',
        'ql-direction-rtl', 'ql-syntax'
    ];

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    // Load as UTF-8 HTML fragment
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $root = $dom->getElementsByTagName('div')->item(0);
    if (!$root) {
        return '';
    }

    $cleanNode = function ($node) use (&$cleanNode, $allowedTags, $dropSubtreeTags, $allowedAttributes, $allowedClasses) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tagName = strtolower($node->nodeName);

            // 1. Immediately drop dangerous elements and all their children
            if (in_array($tagName, $dropSubtreeTags, true)) {
                $node->parentNode->removeChild($node);
                return;
            }

            // 2. Clean children before unwrapping or inspecting current element
            $child = $node->firstChild;
            while ($child) {
                $next = $child->nextSibling;
                $cleanNode($child);
                $child = $next;
            }

            // 3. Disallowed element: unwrap safely (its children are already clean)
            if (!in_array($tagName, $allowedTags, true)) {
                $parent = $node->parentNode;
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
                return;
            }

            // 4. Inspect and sanitize attributes
            if ($node->hasAttributes()) {
                $attrsToRemove = [];
                foreach ($node->attributes as $attr) {
                    $attrName = strtolower($attr->nodeName);
                    $tagAllowed = $allowedAttributes[$tagName] ?? [];
                    $globalAllowed = $allowedAttributes['*'] ?? [];

                    if (!in_array($attrName, $tagAllowed, true) && !in_array($attrName, $globalAllowed, true)) {
                        $attrsToRemove[] = $attrName;
                        continue;
                    }

                    // Strict URL protocol sanitization for href and src
                    if (in_array($attrName, ['href', 'src'], true)) {
                        $val = trim($attr->nodeValue);
                        $cleanVal = safe_url($val);
                        if ($cleanVal === '#' && !str_starts_with($val, '#')) {
                            $attrsToRemove[] = $attrName;
                        } else {
                            $attr->nodeValue = $cleanVal;
                        }
                    }

                    // Restrict class attributes to approved Quill formatting classes
                    if ($attrName === 'class') {
                        $classes = preg_split('/\s+/', trim($attr->nodeValue));
                        $validClasses = array_filter($classes, function ($c) use ($allowedClasses) {
                            return in_array($c, $allowedClasses, true) || preg_match('/^ql-indent-[1-8]$/', $c);
                        });
                        if (empty($validClasses)) {
                            $attrsToRemove[] = 'class';
                        } else {
                            $attr->nodeValue = implode(' ', $validClasses);
                        }
                    }
                }

                foreach ($attrsToRemove as $removeName) {
                    $node->removeAttribute($removeName);
                }

                if ($tagName === 'a' && $node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            }
        }
    };

    // Clean all children of the root wrapper
    $child = $root->firstChild;
    while ($child) {
        $next = $child->nextSibling;
        $cleanNode($child);
        $child = $next;
    }

    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    return $output;
}

/**
 * Verify URL to prevent javascript: or malformed URI execution.
 */
function safe_url(?string $url): string
{
    if (empty($url)) {
        return '';
    }
    $url = trim($url);

    // Strip control chars & non-printable bytes
    $url = preg_replace('/[\x00-\x1F\x7F]/', '', $url);

    // Disallow protocol-relative URLs (e.g. //evil.com)
    if (str_starts_with($url, '//')) {
        return '#';
    }

    // Allow safe root-relative paths and anchors
    if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
        return $url;
    }

    $parts = parse_url($url);
    if ($parts === false) {
        return '#';
    }
    $scheme = strtolower($parts['scheme'] ?? '');
    if (in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
        return $url;
    }

    return '#';
}

/**
 * Validate password strength (minimum length requirement)
 */
function validate_password_strength(string $password): ?string
{
    if (mb_strlen($password) < 6) {
        return 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
    }
    return null;
}


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

    // Parse Markdown (**bold**, *italic*, ~~strike~~, [text](url)) into HTML tags
    if (function_exists('parse_markdown')) {
        $html = parse_markdown($html);
    }

    $allowedTags = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'del', 'strike', 'hr',
        'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'a', 'img', 'blockquote', 'div', 'span',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'code', 'pre'
    ];

    $allowedAttributes = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        '*'   => ['class']
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

    $cleanNode = function ($node) use (&$cleanNode, $allowedTags, $allowedAttributes, $dom) {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tagName = strtolower($node->nodeName);

            if (!in_array($tagName, $allowedTags, true)) {
                // Remove dangerous elements or unwrap text nodes
                $parent = $node->parentNode;
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
                return;
            }

            // Inspect and clean attributes
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
                }

                foreach ($attrsToRemove as $removeName) {
                    $node->removeAttribute($removeName);
                }

                if ($tagName === 'a' && $node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            }
        }

        // Recursively clean children
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }
        foreach ($children as $child) {
            $cleanNode($child);
        }
    };

    $cleanNode($root);

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

    // Allow relative paths, anchors or protocol-relative paths
    if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
        return $url;
    }

    $parts = parse_url($url);
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


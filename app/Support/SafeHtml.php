<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

final class SafeHtml
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'blockquote', 'table', 'thead', 'tbody',
        'tfoot', 'tr', 'th', 'td', 'a', 'hr',
    ];

    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'math',
        'form', 'input', 'button', 'textarea', 'select', 'option',
        'video', 'audio', 'source', 'link', 'meta',
    ];

    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        if (!class_exists(DOMDocument::class)) {
            return e(strip_tags($html));
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<!DOCTYPE html><html><body><div id="safe-rich-text">' . $html . '</div></body></html>',
                LIBXML_NONET | LIBXML_HTML_NODEFDTD
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }

        $xpath = new DOMXPath($document);
        $roots = $xpath->query('//*[@id="safe-rich-text"]');
        $root = $roots?->item(0);

        if (!$root) {
            return e(strip_tags($html));
        }

        foreach (iterator_to_array($root->childNodes) as $child) {
            self::sanitizeNode($child);
        }

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return $output;
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return;
        }

        if (!$node instanceof DOMElement) {
            $node->parentNode?->removeChild($node);
            return;
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
            $node->parentNode?->removeChild($node);
            return;
        }

        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                self::sanitizeNode($child);
            }
            $parent = $node->parentNode;
            if ($parent) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
            }
            return;
        }

        $allowedAttributes = match ($tag) {
            'a' => ['href', 'title'],
            'td', 'th' => ['colspan', 'rowspan', 'scope'],
            default => [],
        };

        foreach (iterator_to_array($node->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            if (!in_array($name, $allowedAttributes, true)) {
                $node->removeAttribute($attribute->name);
                continue;
            }

            if ($tag === 'a' && $name === 'href' && !self::isSafeLink($attribute->value)) {
                $node->removeAttribute('href');
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            self::sanitizeNode($child);
        }
    }

    private static function isSafeLink(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '//') || preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return $scheme === null || in_array(strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true);
    }
}

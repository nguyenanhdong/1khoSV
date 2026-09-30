<?php

namespace frontend\components;

/**
 * Lọc HTML nội dung nhập từ CMS trước khi hiển thị trên web (thay HTMLPurifier 4.10 trong vendor,
 * bản này không chạy được trên PHP 8). Giữ định dạng soạn thảo (TinyMCE), bỏ phần có thể chạy mã.
 */
class HtmlSafe
{
    /** Thẻ bị xoá cả nội dung bên trong */
    const DROP = ['script', 'style', 'object', 'embed', 'applet', 'form', 'input', 'button', 'select', 'textarea',
        'link', 'meta', 'base', 'frame', 'frameset', 'svg', 'math', 'template', 'noscript'];
    /** iframe chỉ cho phép nhúng từ các nguồn này */
    const IFRAME_SRC = '#^(https?:)?//(www\.)?(youtube\.com/embed/|youtube-nocookie\.com/embed/|google\.com/maps/|player\.vimeo\.com/video/)#i';
    const URL_ATTRS = ['href', 'src', 'action', 'formaction', 'poster', 'background', 'xlink:href', 'srcset'];

    public static function clean($html)
    {
        $html = trim((string)$html);
        if ($html === '') {
            return '';
        }
        $doc = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $doc->getElementById('__root');
        if (!$root) {
            return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8');
        }
        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return $out;
    }

    private static function walk(\DOMNode $node)
    {
        // Duyệt trên bản sao danh sách vì có thể xoá node trong lúc duyệt
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment || $child instanceof \DOMProcessingInstruction) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof \DOMElement) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, self::DROP, true)
                || ($tag === 'iframe' && !preg_match(self::IFRAME_SRC, $child->getAttribute('src')))) {
                $node->removeChild($child);
                continue;
            }
            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->nodeName);
                $value = $attr->nodeValue;
                if (strpos($name, 'on') === 0
                    || (in_array($name, self::URL_ATTRS, true) && self::unsafeUrl($value))
                    || ($name === 'style' && preg_match('/expression\s*\(|javascript:|behavior\s*:|url\s*\(\s*[\'"]?\s*(javascript|vbscript|data):/i', $value))) {
                    $child->removeAttribute($attr->nodeName);
                }
            }
            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $child->setAttribute('rel', 'noopener');
            }
            self::walk($child);
        }
    }

    private static function unsafeUrl($url)
    {
        $url = strtolower(preg_replace('/[\x00-\x20]+/', '', html_entity_decode((string)$url, ENT_QUOTES, 'UTF-8')));
        if (strpos($url, 'data:image/') === 0) {
            return false;
        }
        return (bool)preg_match('/^(javascript|vbscript|data|file):/', $url);
    }
}

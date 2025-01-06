<?php

namespace App\Twig;

/**
 * Manipulate data that will be used for the meta tags
 */
class MetatagExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('meta', array($this, 'meta'), array(
                'is_safe' => array('html'),
            ))
        );
    }

    /**
     * Returns an array of meta tags
     */
    public function meta($data)
    {
        // default metatags
        $meta = $data['metatag']['value'] ?? [];
        // node specific metatags
        $nodeMeta = $data['field_meta_tags'][0] ?? [];
        // merge node specific metatag if defined
        if ($nodeMeta) {
            $meta = array_merge($meta, $nodeMeta);
        }

        if (isset($meta['title'])) {
            $title = $meta['title'];
            // remove title from metas
            unset($meta['title']);
        }

        return [
            'title' => $title ?? null,
            'meta' => $meta,
        ];
    }
}

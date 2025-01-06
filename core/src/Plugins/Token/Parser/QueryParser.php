<?php

namespace App\Plugins\Token\Parser;

use App\Plugins\Token\ParserExtensionInterface;

class QueryParser implements ParserExtensionInterface
{
    const PATTERN = '/\[query:\((.*?)\)\]/';

    /**
     * {@inheritdoc}
     */
    public function parse(&$string)
    {
        $callback = function ($matches) {
            if ($matches && count($matches) == 2) {
                list(, $match) = $matches;

                $query = $this->getMatch(html_entity_decode($match));

                if ($query) {
                    return '?' . $query;
                }
            }
        };

        $callback->bindTo($this);

        $string = preg_replace_callback(self::PATTERN, $callback, $string);
    }

    /**
     * Get matches
     *
     * @param array $match
     *
     * @return string
     */
    private function getMatch($match)
    {
        $result = [];
        $lists = explode('&', $match);

        foreach ($lists as $list) {
            $query = explode('=', $list);

            if ($query && count($query) == 2) {
                list($key, $value) = $query;

                // capture if it is zero string or the actual zero integer
                // because ?query=0 should be allowed
                if (!empty($value) || $value === '0' || $value === 0) {
                    $result[$key] = $value;
                }
            }
        }

        if ($result) {
            return http_build_query($result);
        }
    }
}

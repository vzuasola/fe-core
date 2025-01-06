<?php

namespace App\Url\Parser;

class QueryParser implements ParserInterface
{
    const PATTERN = '/\[query:\((.*)\)\]/';

    /**
     * {@inheritdoc}
     */
    public function parse($uri)
    {
        preg_match(self::PATTERN, $uri, $matches);

        if ($matches && count($matches) == 2) {
            list(, $match) = $matches;

            $query = $this->getMatch(html_entity_decode($match));

            if ($query) {
                $prefix = '?' . $query;
                $uri = preg_replace(self::PATTERN, $prefix, $uri);
            } else {
                $uri = preg_replace(self::PATTERN, null, $uri);
            }
        }

        return $uri;
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

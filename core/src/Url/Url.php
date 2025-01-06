<?php

namespace App\Url;

use Psr\Http\Message\ServerRequestInterface;
use App\Negotiation\PathNegotiator;

/**
 * The URL generator
 */
class Url extends Base implements UrlGeneratorInterface
{
    /**
     * The list of allowed protocols.
     *
     * @var array
     */
    const ALLOWED_PROTOCOLS = ['http', 'https'];

    /**
     * Public constructor
     */
    public function __construct(
        ServerRequestInterface $request,
        $lang,
        $product,
        $languages,
        $settings
    ) {
        $this->request = $request;
        $this->lang = $lang;
        $this->product = $product;
        $this->languages = $languages;
        $this->settings = $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function generateUri($uri, $options)
    {
        return $this->generateFromRequest($this->request, $uri, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function generateCanonicalsFromRequest(ServerRequestInterface $request, $uri)
    {
        $languages = $this->languages->getLanguages();

        unset($languages['default']);

        foreach ($languages as $key => $value) {
            $lang = $value['prefix'];

            if ($lang) {
                $prefix = $lang;
                $product = $request->getAttribute('product');

                if ($product) {
                    $prefix = "$prefix/$product";
                }

                $result[] = [
                    'prefix' => $prefix,
                    'id' => $value['id'],
                    'path' => $this->createUri($uri, $prefix),
                ];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFromRequest(ServerRequestInterface $request, $uri, $options)
    {
        // here comes the code for the exclusions
        //
        // @todo If no product is using the exclusions, then we can remove this
        if ($exclusionPrefix = $this->isExcluded($uri, $request)) {
            $prefix = $exclusionPrefix;
        } else {
            $prefix = $request->getAttribute('prefix');

            if (empty($prefix)) {
                $prefix = PathNegotiator::getPrefix($request);
            }

            if (PathNegotiator::getCustom($request, 'empty_language')) {
                $prefix = "";
            }
        }

        return $this->createUri($uri, $prefix, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        // remove lang and product
        $path = $this->stripLanguage($path);
        $path = str_replace($this->product, "", $path);

        // strip slashes
        return trim($path, '/');
    }

    /**
     * Determines whether a path is external
     *
     * Ported from Drupal 8 core
     */
    public function isExternal($path)
    {
        $colonpos = strpos($path, ':');
        // Some browsers treat \ as / so normalize to forward slashes.
        $path = str_replace('\\', '/', $path);
        // If the path starts with 2 slashes then it is always considered an
        // external URL without an explicit protocol part.
        return (strpos($path, '//') === 0)
            // Leading control characters may be ignored or mishandled by browsers,
            // so assume such a path may lead to an external location. The \p{C}
            // character class matches all UTF-8 control, unassigned, and private
            // characters.
            || (preg_match('/^\p{C}/u', $path) !== 0)
            // Avoid calling static::stripDangerousProtocols() if there is any slash
            // (/), hash (#) or question_mark (?) before the colon (:) occurrence -
            // if any - as this would clearly mean it is not a URL.
            || ($colonpos !== false
            && !preg_match('![/?#]!', substr($path, 0, $colonpos))
            && $this->stripDangerousProtocols($path) == $path);
    }

    /**
     *
     */
    private function createUri($uri, $prefix, $options = [])
    {
        $uri = $this->createInitialUri($uri, $prefix);

        // Add http query support
        if (!empty($options['query'])) {
            $query = http_build_query($options['query']);

            if ($query) {
                $query = urldecode($query);
                $uri = "$uri?$query";
            }
        }

        // Parse URL to check if there's multiple "?" in query params.
        $parseUrl = parse_url($uri);
        if (isset($parseUrl['query'])) {
            if (strpos($parseUrl['query'], '?') !== false) {
                $parseUrl['query'] = str_replace('?', '&', $parseUrl['query']);
                if ($this->isToken($uri)) {
                    return ltrim(http_build_url($uri, $parseUrl), '/');
                } else {
                    return http_build_url($uri, $parseUrl);
                }
            }
        }

        if (isset($options['skip_parsers']) && $options['skip_parsers']) {
            // do nothing as we are not allowing any
            // parsers to run
        } else {
            $uri = $this->doParse($uri);
        }

        return $uri;
    }

    /**
     *
     */
    private function createInitialUri($uri, $prefix)
    {
        // The api returns relative links with "internal:/"
        // prefix so we need to manually remove it from the uri.
        $uri = str_replace('internal:', '', $uri);

        // strip slash for relative path to avoid double slashes
        if ($uri !== '/') {
            $uri = ltrim($uri, '/');
        }

        // check if uri is external or token
        if ($this->isExternal($uri) || $this->isToken($uri)) {
            return $uri;
        }

        // strip language prefix on uri since it is provided by
        // the get prefix already when generated
        //
        // @todo Better if we can remove this
        $uri = $this->stripLanguage($uri);

        if ($this->isOnPage($uri)) {
            return $uri;
        }

        // check for mailto and tel links
        if (preg_match('/^(mailto|tel):/', $uri)) {
            return $uri;
        }

        if ($prefix) {
            $prefix = "/$prefix";
        }

        if ($uri == '/') {
            return "$prefix/";
        }

        if ($prefix) {
            return "$prefix/$uri";
        }

        return "/$uri";
    }

    /**
     * General private helper methods
     *
     */

    /**
     * Determines if the path is a pure token value
     */
    private function isToken($path)
    {
        // A pure token path basically starts with a "{"
        // Additionally, pure token path is usually an external/absolute path
        if (strpos($path, '{') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Determines whether the url is current path
     */
    private function isOnPage($path)
    {
        return strpos($path, '#') === 0;
    }

    /**
     *
     */
    private function stripLanguage($uri)
    {
        if ($this->isExternal($uri)) {
            return $uri;
        }

        if (!empty($this->settings['languages']) && isset($this->settings['languages']['supply_languages_list'])) {
            $languages = $this->settings['languages']['supply_languages_list'];
        } else {
            $languages = $this->languages->getLanguages();
        }

        foreach ($languages as $lang) {
            $prefix = $lang['prefix'];

            // check if there's a matching language prefix
            if (preg_match("/^($prefix\/|\/$prefix\/)/", $uri, $matches)) {
                // strip off the language prefix
                // NOTE: This assumes that the prefix is at index 0, should any legitimate uri path
                // that starts with a prefix will be removed.
                $path = substr_replace($uri, '', 0, strlen($matches[0]));

                if ($path) {
                    $uri = ltrim($path, '/');
                    break;
                }
            }
        }

        return $uri;
    }

    /**
     * Check if this path is for exclusions
     */
    private function isExcluded($uri, $request)
    {
        $exclusion = $this->settings['product_exclusions'] ?? null;

        if ($exclusion) {
            $list = array_keys($exclusion);

            foreach ($list as $value) {
                if (strpos($uri, $value) === 0) {
                    $language = $request->getAttribute('language');
                    return $language;
                }
            }
        }
    }

    /**
     * Strip dangerous protocol
     *
     * Ported from Drupal 8 core
     */
    public static function stripDangerousProtocols($uri)
    {
        $allowedProtocols = array_flip(self::ALLOWED_PROTOCOLS);

        // Iteratively remove any invalid protocol found.
        do {
            $before = $uri;
            $colonpos = strpos($uri, ':');

            if ($colonpos > 0) {
                // We found a colon, possibly a protocol. Verify.
                $protocol = substr($uri, 0, $colonpos);
                // If a colon is preceded by a slash, question mark or hash, it cannot
                // possibly be part of the URL scheme. This must be a relative URL, which
                // inherits the (safe) protocol of the base document.
                if (preg_match('![/?#]!', $protocol)) {
                    break;
                }
                // Check if this is a disallowed protocol. Per RFC2616, section 3.2.3
                // (URI Comparison) scheme comparison must be case-insensitive.
                if (!isset($allowedProtocols[strtolower($protocol)])) {
                    $uri = substr($uri, $colonpos + 1);
                }
            }
        } while ($before != $uri);

        return $uri;
    }
}

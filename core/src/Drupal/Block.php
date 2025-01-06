<?php

namespace App\Drupal;

/**
 * Class for block related methods
 */
class Block
{
    /**
     * The current router request object
     *
     * @var object
     */
    private $request;

    /**
     * Public constructor.
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Check if a set of whitelisted page is visible on the current page
     *
     * @param string $pages
     * @param string $customPath
     *
     * @return boolean
     */
    public function isVisibleOn($pages, $customPath = null)
    {
        // if it is blank, show it
        if ($pages === 0) {
            return true;
        }

        $pages = explode(PHP_EOL, $pages);

        $path = $customPath ?? $this->request->getUri()->getPath();
        $path = trim($path);
        // trim trailing slash
        $path = trim($path, '/');

        if ($pages) {
            foreach ($pages as $page) {
                $page = trim($page);
                // trim trailing slash
                $page = trim($page, '/');

                // if it the path is the home page, there is only one
                if ($path == '/') {
                    if ($page == '<front>') {
                        return true;
                    }
                }

                if (fnmatch($page, $path)) {
                    return true;
                }
            }
        }
    }
}

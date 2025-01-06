<?php

namespace App\Utils;

/**
 * Class for handling sort related functionalities
 */
class Menu
{
    /**
     * Set menu link active class
     *
     * @param array $request The raw data request
     * @param array $menu The list of menu links
     */
    public static function setActiveClass($request, $menu)
    {
        $menuLength = count($menu);
        $activeClass = 'active';

        // get the current path and compare it with menu item.
        $urlExplode = explode("/", substr($request->getUri()->getPath(), 1));
        $parentUrl = $urlExplode[0];

        for ($i = 0; $i < $menuLength; $i++) {
            // using stripos because of probability of the token
            if (stripos($menu[$i]['uri'], $parentUrl) !== false ||
                stripos($menu[$i]['alias'], $parentUrl) !== false) {
                $menu[$i][$activeClass] = $activeClass;
            }

            // set the active attribute for the children items.
            if (isset($menu[$i]['below'])) {
                foreach ($menu[$i]['below'] as &$below) {
                    // using stripos because of probability of the token
                    if (stripos($below['uri'], $parentUrl) !== false ||
                        stripos($below['alias'], $parentUrl) !== false) {
                        $below[$activeClass] = $activeClass;
                        $menu[$i][$activeClass] = $activeClass;
                    }
                }
            }
        }

        return $menu;
    }
}

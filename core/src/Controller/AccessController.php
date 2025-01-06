<?php

namespace App\Controller;

use App\BaseController;
use App\Utils\Url;

class AccessController extends BaseController
{
    use BaseSectionTrait;

    /**
     * Unsupported Currency page
     */
    public function unsupportedCurrency($request, $response)
    {
        $translation = $this->get('translation_manager')->getTranslation('unsupported-currency-page');
        $data = $this->pageSections([
            'title' => $translation['title'],
            'apology_message' => $translation['apology_message'],
            'offer_message' => $translation['offer_message'],
            'body_class' => 'page-unsupported-currency'
        ]);
        $data['product_tiles'] = $this->getProductTiles($request, $data['header']['main_menu']);

        return $this->view->render($response, '@site/unsupported-currency-page.html.twig', $data);
    }

    /**
     * Process product tiles
     */
    private function getProductTiles($request, $mainMenu)
    {
        $tiles = [];
        try {
            $keyword = $this->get('product');
            $product = $this->get('settings')['product'];

            // This will only exist if site maintenance middleware is enabled.
            $siteProduct = $request->getAttribute('site_product');

            foreach ($mainMenu as $key => $menu) {
                $alias = $menu['alias'] ?? $menu['uri'];
                $tile = [
                    'title' => $menu['title'] ?? 'Menu',
                    'alias' => Url::generateFromRequest($request, $alias),
                ];

                // We presume that the alias is already parsed
                $segments = explode("/", parse_url($tile['alias'], PHP_URL_PATH));
                if (strpos($tile['alias'], $keyword) !== false) {
                    // Check if the current keyword within the alias (URL) is a keyword of the main site
                    // If this is the case use check if the mapped site product exists
                    // If not, use the product key in settings.php instead
                    $tile['class'] = $siteProduct ?? $product;
                } else {
                    // Clean any additional segment
                    $tile['class'] = str_replace('/', '', $segments[2] ?? 'casino');
                }

                $tiles[] = $tile;
            }
        } catch (\Exception $e) {
            // Do nothing
        }

        return $tiles;
    }
}

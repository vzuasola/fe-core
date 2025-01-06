<?php

namespace App\Token;

use App\Plugins\Token\TokenInterface;
use Interop\Container\ContainerInterface;

use App\Drupal\Config;

/**
 *
 */
class CashierToken implements TokenInterface
{
    private $player;
    private $config;
    private $parser;

    /**
     * Dependency injection
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->player = $container->get('player');
        $this->configs = $container->get('config_fetcher');
        $this->parser = $container->get('token_parser');
    }

    /**
     * Returns the replacement data for this specific token class
     */
    public function getToken($options)
    {
        $headerConfigs = $this->configs->getConfig('webcomposer_config.header_configuration');

        if (!empty($headerConfigs['cashier_mapping'])) {
            $mapping = Config::parseMultidimensional($headerConfigs['cashier_mapping']);

            if ($mapping) {
                try {
                    $currency = $this->player->getCurrency();
                    $country = $this->player->getCountryCode();

                    $currency = strtoupper($currency);
                    $country = strtoupper($country);

                    if (isset($mapping[$currency][$country])) {
                        $link = $mapping[$currency][$country];
                    } elseif (isset($mapping[$currency]['*'])) {
                        $link = $mapping[$currency]['*'];
                    }
                } catch (\Exception $e) {
                    // do nothing
                }
            }
        }

        if (empty($link)) {
            $link = $headerConfigs['default_cashier_link'] ?? null;
        }

        return $this->parser->processTokens($link);
    }
}

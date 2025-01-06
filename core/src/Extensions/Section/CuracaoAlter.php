<?php

namespace App\Extensions\Section;

use App\Plugins\Section\AsyncSectionAlterInterface;
use App\Utils\Host;

class CuracaoAlter implements AsyncSectionAlterInterface
{
    /**
     * List of whitelisted domains
     */
    const DOMAINS = [
        'www.dafabet.com',
        'www.zipangcasino.com',
        'www.777baby.com',
        'www.casinojamboree.com',
        'stg-fe-www.elysium-zpngcsn.com',
        'stg-www.777baby.net'
    ];

    /**
     *
     */
    public function setContainer($container)
    {
        $this->scripts = $container->get('scripts');
        $this->request = $container->get('router_request');
    }

    /**
     * {@inheritdoc}
     */
    public function alterSectionDefinition(&$definitions, array $options)
    {
        return $definitions;
    }

    /**
     * {@inheritdoc}
     */
    public function alterprocessDefinition(&$result, $data, array $options)
    {
        if ($this->isAllowed()) {
            $marketingScript = isset($result['curacao']['status']) && !empty($result['curacao']['status']);
            $scriptPath = isset($result['curacao']['script_path']) && !empty($result['curacao']['script_path']);

            if (!$marketingScript && $scriptPath) {
                $this->scripts->add($result['curacao']['script_path']);
            }

            if (isset($result['partners'])) {
                foreach ($result['partners'] as $key => $item) {
                    if (isset($item['field_id'][0]['value']) && $item['field_id'][0]['value'] == 'curacao') {
                        $result['partners'][$key]['raw'] = $result['curacao']['markup'];
                        $result['partners'][$key]['raw_and_image'] = 1;

                        $this->scripts->attach([
                            'enable_marketing_script' => $result['curacao']['status'] ?? 0,
                            'curacao_script' => $result['curacao']['script_path'] ?? '',
                        ]);
                    }
                }
            }
        }
    }

    /**
     *
     */
    private function isAllowed()
    {
        $default = Host::getHostname((string) $this->request->getUri());

        foreach (self::DOMAINS as $domain) {
            if ($domain === $default) {
                return true;
            }
        }
    }
}

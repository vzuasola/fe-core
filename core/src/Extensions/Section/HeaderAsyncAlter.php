<?php

namespace App\Extensions\Section;

use App\Plugins\Section\AsyncSectionAlterInterface;

class HeaderAsyncAlter implements AsyncSectionAlterInterface
{
    /**
     * Indonesian Domains where header section updates will be applied.
     *
     * @var array
     */
    private $indonesianDomains = [
        'd8bola.com', 'www.d8bola.com',
        'd8bola.net', 'www.d8bola.net',
        'd8id.com', 'www.d8id.com',
        'd8id.net', 'www.d8id.net',
        'd8gol.com', 'www.d8gol.com',
        'golemas.com', 'www.golemas.com',
        'bolaindo8.com', 'www.bolaindo8.com',
        'pialadunia888.com', 'www.pialadunia888.com',
        'qa3-www.elysium-dfbt.com', 'qa3-www.elysium-pkr.com',
        'stg3-www.elysium-dfbt.com', 'stg3-www.elysium-pkr.com',
    ];

    /**
     * Languages with logo override
     *
     * @var array
     */
    private $logoLanguages = ['es','pt'];

    /**
     * Dependency injection
     */
    public function setContainer($container)
    {
        $this->lang = $container->get('lang');
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
        if (in_array($result['host'], $this->indonesianDomains)) {
            // Remove language selector
            $result['language_disabled'] = true;
        }

        // LATAM logo redirection
        if (in_array($this->lang, $this->logoLanguages)) {
            // Change link
            $result['logo_uri'] = "/{lang}[query:({tracking}&{tracking.adavice})]";
        }
    }
}

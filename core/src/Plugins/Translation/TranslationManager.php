<?php

namespace App\Plugins\Translation;

use Symfony\Component\Yaml\Yaml;

/**
 * Translation Manager
 */
class TranslationManager
{
    /**
     * The current language code
     *
     * @var string
     */
    private $lang;

    /**
     * The language fetcher
     *
     * @var object
     */
    private $languages;

    /**
     * Public constructor
     */
    public function __construct($lang, $languages)
    {
        $this->lang = $lang;
        $this->languages = $languages;
    }

    /**
     * Gets translation by translation ID
     *
     * @param string $id The translation config name
     * @param array options Additional options to be passed
     *
     * @return string
     */
    public function getTranslation($id, $options = [])
    {
        $translation = $this->getFileContent($id);

        if (!empty($translation)) {
            return $translation['translations'][$this->lang] ?? $translation['default'] ?? null;
        }
    }

    /**
     * Gets default translation
     *
     * @param string $id The translation config name
     * @param array options Additional options to be passed
     *
     * @return string
     */
    public function getDefaultTranslation($id, $options = [])
    {
        $translation = $this->getFileContent($id);

        if (!empty($translation)) {
            return  $translation['default'] ?? null;
        }
    }

    /**
     * Gets specific translation
     *
     * @param string $id The translation config name
     * @param string $lang The language code
     * @param array options Additional options to be passed
     *
     * @return string
     */
    public function getTranslationByLanguage($id, $lang, $options = [])
    {
        $translation = $this->getFileContent($id);

        if (!empty($translation)) {
            return $translation['translations'][$lang] ?? null;
        }
    }

    /**
     * Gets all translations for a particular file
     *
     * @param string $id The translation config name
     * @param array options Additional options to be passed
     *
     * @return array
     */
    public function getTranslations($id, $options = [])
    {
        $translation = $this->getFileContent($id);

        if (!empty($translation)) {
            $result = [];
            $languages = $this->languages->getLanguages();

            foreach ($languages as $key => $value) {
                $key = $value['prefix'];

                if (isset($translation['translations'][$key])) {
                    $result[$key] = $translation['translations'][$key];
                }
            }

            return $result;
        }
    }

    /**
     *
     */
    protected function getFileContent($id)
    {
        $translation = [];

        if (file_exists(APP_ROOT . "/core/src/Resources/translation/$id.yml")) {
            $file = file_get_contents(APP_ROOT . "/core/src/Resources/translation/$id.yml");
            $translation = Yaml::parse($file);
        }

        if (file_exists(CONFIG_ROOT . "/../../src/Resources/translation/$id.yml")) {
            $file = file_get_contents(CONFIG_ROOT . "/../../src/Resources/translation/$id.yml");
            $translation = Yaml::parse($file);
        }

        return $translation;
    }
}

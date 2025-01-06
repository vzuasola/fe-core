<?php

namespace App\Javascript\Providers;

use App\Plugins\Javascript\ScriptProviderInterface;

/**
 *
 */
class Adelement implements ScriptProviderInterface
{
    /**
     * Adelement salt
     */
    const SALT = 'v4l4rm0rghul1$!g0tv4l4rd0h43r1$!';

    /**
     * Sets the container
     */
    public function setContainer($container)
    {
        $this->config = $container->get('config_fetcher_async');
        $this->player = $container->get('player_session');
    }

    /**
     * @{inheritdoc}
     */
    public function getAttachments()
    {
        $data = [];

        $providers = $this->config
            ->getConfig('webcomposer_marketing_script.providers')
            ->resolve();

        if (isset($providers['adelement']) && $this->player->isLogin()) {
            $username = $this->player->getUsername();
            $data['marketing_scripts'] = $providers;
            $data['marketing_scripts']['adelement']['username'] = $this->encode($username);
        }

        return $data;
    }

    /**
     * Encode params
     *
     * @todo Use a different encryption to avoid warnings
     * @param  string $value
     * @return string $username
     */
    private function encode($value)
    {
        if ($value) {
            $text = $value;
            $iv_size = @mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $iv = @mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $crypttext = @mcrypt_encrypt(MCRYPT_RIJNDAEL_256, self::SALT, $text, MCRYPT_MODE_ECB, $iv);

            $data = base64_encode($crypttext);
            $data = str_replace(['+', '/', '='], ['-', '_', ''], $data);

            return trim($data);
        }
    }
}

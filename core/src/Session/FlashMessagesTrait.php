<?php

namespace App\Session;

trait FlashMessagesTrait
{
    private $isRequestHasFlash = false;

    /**
     * {@inheritdoc}
     */
    public function getAllFlashes()
    {
        if (!isset($_SESSION)) {
            session_start([
                'cookie_domain' => $this->domain,
            ]);
        }

        $flash = $_SESSION[self::FLASH_PREFIX][$this->product] ?? null;

        if ($flash) {
            unset($_SESSION[self::FLASH_PREFIX][$this->product]);
        }

        return $flash;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFlash($index)
    {
        $this->checkSessionState();

        return isset($this->session[self::FLASH_PREFIX][$this->product][$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasFlashes()
    {
        $this->checkSessionState();

        return !empty($this->session[self::FLASH_PREFIX][$this->product]);
    }

    /**
     * {@inheritdoc}
     */
    public function requestHasFlashes()
    {
        return $this->isRequestHasFlash;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlash($index)
    {
        session_start([
            'cookie_domain' => $this->domain,
        ]);

        $flash = $_SESSION[self::FLASH_PREFIX][$this->product][$index] ?? null;

        if ($flash) {
            unset($_SESSION[self::FLASH_PREFIX][$this->product][$index]);
            $this->isRequestHasFlash = true;
        }

        session_write_close();

        return $flash;
    }

    /**
     * {@inheritdoc}
     */
    public function setFlash($index, $value)
    {
        session_start([
            'cookie_domain' => $this->domain,
        ]);

        $this->isRequestHasFlash = true;
        $_SESSION[self::FLASH_PREFIX][$this->product][$index] = $value;

        session_write_close();
    }
}

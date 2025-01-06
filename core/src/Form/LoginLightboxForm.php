<?php

namespace App\Form;

use App\Plugins\Form\FormInterface;
use App\Fetcher\Integration\Exception\AccountLockedException;
use App\Fetcher\Integration\Exception\AccountSuspendedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Utils\Url;

/**
 *
 */
class LoginLightboxForm implements FormInterface
{
    /**
     *
     */
    private $handler;

    /**
     *
     */
    private $request;

    /**
     *
     */
    private $playerSession;

    /**
     *
     */
    private $langcode;

    /**
     *
     */
    private $product;

    /**
     * Set Service Container
     */
    public function setContainer($container)
    {
        $this->handler = $container->get('handler');
        $this->request = $container->get('router_request');
        $this->playerSession = $container->get('player_session');
        $this->product = $container->get('product');
        $this->langcode = $container->get('lang');
        $this->settings = $container->get('settings');
    }

    /**
     * Build login lightbox form
     */
    public function getForm(FormBuilderInterface $form, $options)
    {
        $action = Url::generateFromRequest($this->request, 'ajax/login');
        $destination = $this->request->getUri()->getPath();

        $form->setMethod('POST')
            ->setAction($action)
            ->add('username', TextType::class, array('required' => false))
            ->add('password', PasswordType::class, array('required' => false))
            ->add('destination', HiddenType::class, array(
                'data' => $destination
            ))
            ->add('product', HiddenType::class, [
                'data' => $this->settings['product']
            ])
            ->add('submit', SubmitType::class);
    }

    /**
     * Login lightbox form submit handler
     */
    public function submit(
        $form,
        $options,
        ResponseInterface $response = null
    ) {
        $success = false;

        $username = $form->get('username')->getData();
        $password = $form->get('password')->getData();

        $destination = $form->get('destination')->getData();
        $product = $form->get('product')->getData();

        if (isset($product)) {
            $options['header']['Login-Product'] = $product;
        }

        try {
            $success = $this->playerSession->login($username, $password, $options);
        } catch (\Exception $e) {
            if ($e instanceof AccountLockedException) {
                $handler = $this->handler->getEvent('account_locked');
                return $handler($this->request, $response, $username, $destination, true);
            }

            if ($e instanceof AccountSuspendedException) {
                $handler = $this->handler->getEvent('account_suspended');
                return $handler($this->request, $response, $username, $destination, true);
            }

            if ($e->getCode() == 401) {
                $handler = $this->handler->getEvent('login_failed');
                return $handler($this->request, $response, $username, $destination, true);
            }

            if ($e->getCode() == 500) {
                $handler = $this->handler->getEvent('service_not_available');
                return $handler($this->request, $response, $username, $destination, true);
            }
        }

        if ($success) {
            $handler = $this->handler->getEvent('login_success');
            return $handler($this->request, $response, $username, $destination, true);
        }

        $handler = $this->handler->getEvent('login_failed');
        return $handler($this->request, $response, $username);
    }
}

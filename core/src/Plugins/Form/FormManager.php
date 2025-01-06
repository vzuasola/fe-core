<?php

namespace App\Plugins\Form;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Plugins\Form\FormInterface;

use App\Symfony\Form\Type\MarkupType;
use App\Symfony\Form\Type\FieldsetType;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

/**
 * Form Manager for calling Symfony forms
 */
class FormManager
{
    /**
     * Exposed the service container on the form manager
     */
    protected $container;

    /**
     * The monolog logger object
     *
     * @var object
     */
    private $logger;

    /**
     * List of form configurations
     *
     * @var array
     */
    private $configurations;

    /**
     * Public constructor.
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');

        $configuration = $container->get('configuration_manager');
        $this->configurations = $configuration->getConfiguration('forms');
    }

    /**
     * Gets the form using a form ID
     *
     * @param string $formId  The form class name
     * @param array  $options Additional options to be passed to the form builder
     */
    public function getForm($formId, $options = [], $product = null)
    {
        $form = new $formId($product);

        // append configuration to options
        $options['configurations'] = $this->configurations;

        $formDefinition = $this->getFormDefinition($form, $formId, $options);

        return $formDefinition;
    }

    /**
     *
     */
    private function getFormDefinition(
        FormInterface $form,
        $formId,
        $options
    ) {
        // inject the service container
        if (method_exists($form, 'setContainer')) {
            $form->setContainer($this->container);
        }

        // We'll attach the validators on the form tag
        if (method_exists($form, 'getValidatorsByFormId')) {
            $options['validators'] = $form->getValidatorsByFormId($formId);
        }

        // create the form instance
        $formInstance = $this->createFormInstance($formId, $options);

        // let the individual form class manage the addition of form fields
        $form->getForm($formInstance, $options);

        $formDefinition = $formInstance->getForm();

        // handle the request and forward it to the individual form's submit
        // handler
        $formDefinition->handleRequest();


        if ($formDefinition->isSubmitted() && $formDefinition->isValid()) {
            $this->doSubmit($form, $formDefinition, $options);
            $this->log($formId);

            // workaround for clearing the form
            // reinitiate the form object from scratch
            if (isset($formDefinition->clear) && $formDefinition->clear) {
                $formInstance = $this->createFormInstance($formId);
                $form->getForm($formInstance, $options);
                $formDefinition = $formInstance->getForm();
            }
        }

        return $formDefinition;
    }

    /**
     *
     */
    public function handleSubmission(
        ResponseInterface $response,
        $formId,
        $options = []
    ) {
        $form = new $formId();

        // inject the service container
        if (method_exists($form, 'setContainer')) {
            $form->setContainer($this->container);
        }
        $formInstance = $this->createFormInstance($formId);

        // let the individual form class manage the addition of form fields
        $form->getForm($formInstance, $options);

        $formDefinition = $formInstance->getForm();

        // handle the request and forward it to the individual form's submit
        // handler
        $formDefinition->handleRequest();

        if ($formDefinition->isSubmitted() && $formDefinition->isValid()) {
            $response = $this->doSubmit($form, $formDefinition, $options, $response);

            $this->log($formId);
        } else {
            // check if form has a special invalid form handler
            if (method_exists($form, 'onInvalidSubmit')) {
                $response = $form->onInvalidSubmit($formDefinition, $options, $response);
            }
        }

        return $response;
    }

    /**
     * Form Generation
     *
     */

    /**
     *
     */
    private function createFormInstance($formId, $options = [])
    {
        // $security = $this->getSecurityManager();
        $formFactory = Forms::createFormFactoryBuilder()
            // ->addExtension(new CsrfExtension($security))
            ->addType(new MarkupType())
            ->addType(new FieldsetType())
            ->getFormFactory();

        $formName = $this->getFormName($formId);

        // Override Form Name
        if (isset($options['form_name'])) {
            $formName = $options['form_name'];
        }

        if (!empty($options['validators'])) {
            $formInstance = $formFactory->createNamedBuilder(
                $formName,
                'Symfony\Component\Form\Extension\Core\Type\FormType',
                [],
                ['attr' =>
                    [
                        'data-validations' => json_encode($options['validators']),
                    ],
                ]
            );
        } else {
            $formInstance = $formFactory->createNamedBuilder($formName);
        }

        return $formInstance;
    }

    /**
     * Generates a security token manager
     *
     * @return CsrfTokenManager
     */
    // private function getSecurityManager()
    // {
    //     $generator = new UriSafeTokenGenerator();
    //     $storage = new NativeSessionTokenStorage();
    //     $manager = new CsrfTokenManager($generator, $storage);

    //     return $manager;
    // }

    /**
     * Gets the class name of the formId
     *
     * @param string $formId
     *
     * @return string
     */
    private function getFormName($formId)
    {
        return (new \ReflectionClass($formId))->getShortName();
    }

    /**
     * Form Submissions
     *
     */

    /**
     *
     */
    private function doSubmit($form, $formDefinition, $options, ResponseInterface $response = null)
    {
        if (method_exists($form, 'preSubmit')) {
            try {
                $form->preSubmit($formDefinition, $options, $response);
            } catch (\Exception $e) {
                return;
            }
        }

        $response = $form->submit($formDefinition, $options, $response);

        return $response;
    }

    /**
     * Log handling
     *
     */

    /**
     * Custom logging method
     *
     * @param string $formId
     */
    private function log($formId)
    {
        $this->logger->info('form_submit', [
            'component' => 'Form Submit - ' . __CLASS__,
            'source' => $formId,
            'username' => '',
            'action' => 'Form Request Handler',
            'object' => '',
            'status' => 'Form submission attempted',
            'response' => '',
        ]);
    }
}

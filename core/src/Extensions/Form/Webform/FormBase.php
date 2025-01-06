<?php

namespace App\Extensions\Form\Webform;

use App\Plugins\Form\FormInterface;
use App\Extensions\Form\Webform\Exception\FormInvalidException;
use App\Extensions\Form\Webform\Exception\SubmissionException;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 *
 */
class FormBase implements FormInterface
{
    /**
     * This adds a preSubmit method to trigger the server side validation
     */
    use ServerValidationTrait;

    /**
     *
     */
    const DEFAULT_SUCCESS_MESSAGE = 'Form submission is successful';

    /**
     *
     */
    const DEFAULT_FAILED_MESSAGE = 'The form submission has failed';

    /**
     *
     */
    const DEFAULT_CLOSE_MESSAGE = 'The form is closed';

    /**
     *
     */
    const DEFAULT_LIMIT_MESSAGE = 'Limit for submission has been reached';

    /**
     * The product from which we want to fetch the webform.
     */
    private $product;

    public function __construct($product = null)
    {
        $this->product = $product;
    }

    /**
     *
     */
    public function setContainer($container)
    {
        $this->form = $container->get('form_fetcher');
        if (!is_null($this->product)) {
            $this->form = $this->form->withProduct($this->product);
        }

        $this->session = $container->get('session');
        $this->scripts = $container->get('scripts');
        $this->logger = $container->get('logger');
        $this->sms = $container->get('sms_fetcher');
    }

    /**
     *
     */
    public function getForm(FormBuilderInterface $form, $options)
    {
        $id = $options['formId'];
        $settings = $this->form->getDataById($id);

        // break on invalid webforms
        if (!isset($options['formId'])) {
            throw new FormInvalidException('Requested a webform but no ID key was passed on the options');
        }

        $id = $options['formId'];

        // we unset this because we are passing options as a Symfony form field
        // option
        unset($options['formId']);

        $form = $this->form->getFormById($id, $form, $options);
    }

    /**
     *
     */
    public function submit($form, $options)
    {
        $id = $options['formId'];
        $settings = $this->form->getDataById($id);
        $webformsms = $settings['third_party_settings']['webcomposer_webform']['webform_sms'];

        $submission = $form->getData();

        try {
            // submission alteration
            if (isset($options['submission_alter']) &&
                is_callable($options['submission_alter'])
            ) {
                $callable = $options['submission_alter'];
                $callable($submission);
            }

            $response = $this->form->sumbitFormById($id, $submission);

            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);

            $body = $data['body'];

            $valid = $this->validateResponse($form, $body);

            if ($valid) {
                if (!$this->sendSms($id, $submission['mobile_number'], $webformsms)) {
                    $message = $webformsms['sms_error'];
                    $form->addError(new FormError($message));
                    return;
                }

                $this->session->setFlash('webform.success', $valid);
                $form->clear = true;
            }
        } catch (\Exception $e) {
            $message = self::DEFAULT_FAILED_MESSAGE;

            $form->addError(new FormError($message));
        }
    }

    private function sendSms($id, $mobile_number, $sms)
    {
        // SMS
        if ($sms['sms']) {
            try {
                $smsData = [
                    'id' => $id,
                    'to' => str_replace('+', '', $mobile_number),
                    'message' => $sms['sms_message'],
                    'max_per_ip' => $sms['max_per_ip'],
                    'max_per_number' => $sms['max_per_number']
                ];

                $this->sms->sendSms($smsData);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the response is valid
     */
    private function validateResponse($form, $response)
    {
        // capture response based on scenario
        switch (true) {
            case !empty($response['sid']):
                return $response['confirmation_message'] ?: self::DEFAULT_SUCCESS_MESSAGE;

            case isset($response['limit_total_message']):
                $message = $response['limit_total_message'] ?: self::DEFAULT_LIMIT_MESSAGE;
                $form->addError(new FormError($message));
                return;

            case isset($response['form_close_message']):
                $message = $response['form_close_message'] ?: self::DEFAULT_CLOSE_MESSAGE;
                $form->addError(new FormError($message));
                return;
        }

        $this->logger->error('webform_submit_exception', [
            'response' => $response,
        ]);

        throw new SubmissionException('Form submission exception');
    }
}

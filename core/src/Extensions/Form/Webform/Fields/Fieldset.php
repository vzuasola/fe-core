<?php

namespace App\Extensions\Form\Webform\Fields;

use App\Plugins\Form\Field\FormMapInterface;
use App\Symfony\Form\Type\FieldsetType;
use App\Fetcher\Drupal\FormBuilderFetcher;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class dedicated for the fieldset field specfications
 */
class Fieldset extends BaseDefinition implements FormMapInterface
{
    private $definitions;
    private $class;

    /**
     * Return the class equivalent of Drupal choice field to Symfony
     */
    public function type()
    {
        return FieldsetType::class;
    }

    /**
     *
     */
    public function build($builder, $name, $key, $fields, $options)
    {
        $field = $fields[$key];

        $children = $this->children($field);

        $this->definitions = $builder->getFormDefinition($name, $children, $options);
    }

    /**
     * Specify custom options that you need for choice field
     */
    public function options($fields, $configurations)
    {
        $options = [];

        $this->class = $fields['#class'] ?? null;
        $this->legend = $fields['#legend'] ?? null;

        return $options;
    }

    /**
     * Alter the definitions
     */
    public function alterDefinitions($name, $key, &$definitions)
    {
        unset($definitions[$name][$key]);

        $definitions[$name]["open_$key"] = [
            'type' => FieldsetType::class,
            'options' => [
                'wrapper' => 'open',
                'class' => $this->class,
                'legend' => $this->legend,
            ],
        ];

        $definitions = array_replace_recursive($definitions, $this->definitions);

        $definitions[$name]["close_$key"] = [
            'type' => FieldsetType::class,
            'options' => [
                'wrapper' => 'close',
            ],
        ];
    }

    /**
     *
     */
    private function children($elements)
    {
        $children = [];

        foreach ($elements as $key => $value) {
            if ($key[0] !== '#') {
                if (is_array($value)) {
                    $children[$key] = $value;
                }
            }
        }

        return $children;
    }
}

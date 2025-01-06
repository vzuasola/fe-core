<?php

namespace App\Monolog\Formatter;

use Monolog\Formatter\JsonFormatter as Base;
use App\Monolog\Workflows;

class JsonFormatter extends Base
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $stash = [];
        $context = $record['context'];

        foreach (Workflows::FIELDS as $key) {
            if (isset($context[$key])) {
                $stash[$key] = $context[$key];
            }
        }

        return parent::format($stash);
    }
}

<?php

namespace App\Monolog\Formatter;

use App\Monolog\Workflows;
use Monolog\Formatter\NormalizerFormatter;

/**
 *
 */
class PipeFormatter extends NormalizerFormatter
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
                $stash[$key] = $this->normalizeValue($context[$key]);
            } else {
                $stash[$key] = '';
            }
        }

        return implode('|', $stash) . PHP_EOL;
    }

    /**
     * @param  mixed $value
     * @return mixed
     */
    protected function normalizeValue($value)
    {
        $normalized = $this->normalize($value);

        if (is_array($normalized) || is_object($normalized)) {
            return $this->toJson($normalized, true);
        }

        return $normalized;
    }
}

<?php

namespace App\Plugins\ComponentWidget\Render;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Stream;

use App\Async\AsyncResolver;

use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;

trait RendererTrait
{
    /**
     *
     */
    private function renderModules($output, $response)
    {
        $callback = function () {
            $options = [];

            return $this->manager->renderModules($options);
        };

        $callback->bindTo($this);

        $raw = (string) $output->getBody();

        // we are matching all strings with <# MODULE #>
        $body = preg_replace_callback("/\<# MODULE #>/", $callback, $raw);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $body);

        $newStream = new Stream($stream);

        return $response->withBody($newStream);
    }

    /**
     *
     */
    private function renderModuleIncludes($output, $response)
    {
        $callback = function () {
            $options = [];

            return $this->manager->renderModuleIncludes($options);
        };

        $callback->bindTo($this);

        $raw = (string) $output->getBody();

        // we are matching all strings with <# MODULE_SCRIPTS #>
        $body = preg_replace_callback("/\<# MODULE_SCRIPTS #>/", $callback, $raw);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $body);

        $newStream = new Stream($stream);

        return $response->withBody($newStream);
    }

    /**
     *
     */
    private function renderComponents($output, $response, $instances, $data, $options = [])
    {
        $callback = function ($matches) use ($instances, $data, $options) {
            if (count($matches) == 2) {
                list(, $key) = $matches;

                $options = array_replace(
                    $options,
                    $instances[$key]['options'] ?? []
                );

                return $this->manager->renderWidget($key, $options);
            }
        };

        $callback->bindTo($this);

        $raw = (string) $output->getBody();

        // we are matching all strings with <# WIDGET(component_id) #>
        $body = preg_replace_callback("/\<# WIDGET\(([^ \n\r\"]*?)\) #>/", $callback, $raw);

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $body);

        $newStream = new Stream($stream);

        return $response->withBody($newStream);
    }

    /**
     *
     */
    private function resolveAsync($instances)
    {
        $definitions = [];

        foreach ($instances as $key => $instance) {
            if (isset($instance['async'])) {
                $definitions = array_merge(
                    $definitions,
                    $instance['async']->getDefinitions()
                );
            }
        }

        $options = [];
        $promises = [];

        $preDefinitions = $this->resolver->resolve($definitions);

        foreach ($preDefinitions as $key => $value) {
            $definition = $value->definition;
            $key = $definition->getMethod() . ':' . $definition->getUri();

            $options[$key] = $definition->getOptions();
            $promises[$key] = $value;
        }

        $results = Promise\settle($promises)->wait();

        foreach ($results as $key => $value) {
            if (isset($value['value']) && $value['value'] instanceof Response) {
                $this->cacher->set($key, $value['value'], $options[$key]);
            }
        }
    }
}

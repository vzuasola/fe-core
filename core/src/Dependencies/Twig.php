<?php

namespace App\Dependencies;

use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;

use Slim\Views\Twig as SlimTwig;

/**
 *
 */
class Twig
{
    /**
     *
     */
    public function __invoke($container)
    {
        $debug = $container->get('settings')['debug'];
        $settings = $container->get('settings')['renderer'];

        $appVariableReflection = new \ReflectionClass('\Symfony\Bridge\Twig\AppVariable');
        $vendorTwigBridgeDir = dirname($appVariableReflection->getFileName());

        $settings['template_path']
            [\Twig_Loader_Filesystem::MAIN_NAMESPACE] = $vendorTwigBridgeDir . '/Resources/views/Form';

        $view = new SlimTwig($settings['template_path'], [
            'cache' => $settings['cache_path'],
            'debug' => $debug,
        ]);

        $twig = $view->getEnvironment();
        $twig->addGlobal('app', $container);

        // Add router specific parameters to global namespace
        $routerData = [
            'path' => $container->get('router_request')->getUri()->getPath(),
        ];

        $twig->addGlobal('router', $routerData);

        $defaultFormTheme = 'form_div_layout.html.twig';
        $formEngine = new TwigRendererEngine([$defaultFormTheme], $twig);

        $twig->addRuntimeLoader(
            new \Twig_FactoryRuntimeLoader([
                FormRenderer::class => function () use ($formEngine) {
                    return new FormRenderer($formEngine);
                },
            ])
        );

        $this->processExtensions($view, $container);

        return $view;
    }

    /**
     *
     */
    private function processExtensions($view, $container)
    {
        $uri = str_ireplace('index.php', '', $container['request']->getUri()->getBasePath());
        $basePath = rtrim($uri, '/');
        $settings = $container->get('settings');

        $view->addExtension(new \Slim\Views\TwigExtension($container['router'], $basePath));
        $view->addExtension(new \App\Twig\KintExtension());
        $view->addExtension(new \Symfony\Bridge\Twig\Extension\FormExtension());
        $view->addExtension(new \App\Twig\TranslationExtension());
        $view->addExtension(new \App\Twig\MetatagExtension());
        $view->addExtension(new \App\Twig\WidgetExtension($container['component_widget_manager']));

        if ($container->has('router_request')) {
            $view->addExtension(new \App\Twig\LinkExtension($container['router_request']));
            $view->addExtension(
                new \App\Twig\SnippetExtension(
                    $container['snippet_fetcher'],
                    $container['router_request']
                )
            );
        } else {
            // This will only happen when there is fatal error or request errors in one of the integration
            $view->addExtension(new \App\Twig\LinkExtension($container['request']));
            $view->addExtension(new \App\Twig\SnippetExtension($container['snippet_fetcher'], $container['request']));
        }

        if (isset($settings['view_extension']) && !empty($settings['view_extension'])) {
            foreach ($settings['view_extension'] as $viewExtension) {
                if (class_exists($viewExtension)) {
                    $view->addExtension(new $viewExtension());
                }
            }
        }
    }
}

<?php

namespace App\Controller;

use App\BaseController;
use App\Exception\ResponseException;
use Slim\Exception\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;

abstract class NodeController extends BaseController
{
    /**
     * Overridable default twig template;
     *
     * @var string
     */
    protected $defaultTemplate = '@site/node.html.twig';

    /**
     * Define a view node method for generic page and node handling
     */
    abstract public function viewNode($request, $response, $args, $data);

    /**
     * Redirect method by content type
     *
     * This method allows you to define method of format 'viewNode{Type}' to be
     * defined on your controller
     */
    public function viewNodeByType($request, $response, $args)
    {
        $path = $request->getUri()->getPath();
        $path = trim($path, '/');

        try {
            $data = $this->get('node_fetcher')->getNodeByAlias($path);
        } catch (\Exception $e) {
            throw new NotFoundException($request, $response);
        }

        // let's check if this node is really a valid node with a content type
        if (isset($data['nid'][0]['value']) && isset($data['type'][0]['target_id'])) {
            $type = $data['type'][0]['target_id'];
            $type = str_replace('_', '', ucwords($type, '_'));

            $method = "viewNode$type";

            if (method_exists($this, $method)) {
                return $this->$method($request, $response, $args, $data);
            }
        }

        // else use a generic node method
        $this->viewNode($request, $response, $args, $data);
    }

    /**
     * Fetches content by content type
     *
     * This route allows you to define twig templates in
     * '/site/node/{content_type}.html.twig'
     * where various content types will be route to specific templates
     */
    public function renderNodeByContentType($request, $response, $args)
    {
        $path = $request->getUri()->getPath();
        $path = trim($path, '/');

        try {
            $node = $this->get('node_fetcher')->getNodeByAlias($path);
        } catch (\Exception $e) {
            throw new NotFoundException($request, $response);
        }

        $body = $node->getBody()->getContents();
        $data = json_decode($body, true)['body'];

        // let's check if this node is really a valid node with a content type
        if (isset($data['nid'][0]['value']) && isset($data['type'][0]['target_id'])) {
            $type = $data['type'][0]['target_id'];
            $template = "@site/node/$type.html.twig";

            if (! $this->view->getLoader()->exists($template)) {
                $template = $this->defaultTemplate;
            }

            return $this->view->render($response, $template, [
                'data' => $data
            ]);
        }

        throw new NotFoundException($request, $response);
    }
}

<?php

namespace App\Fetcher\Integration;

use GuzzleHttp\Client;
use App\Fetcher\LogTrait;
use App\Fetcher\AbstractFetcher;
use App\Async\Async;

/**
 * JIRA Integration Library class
 */
class JIRAFetcher extends AbstractFetcher
{
    use LogTrait;

    private const WEBSERVICEROOT = 'https://asianlogic.atlassian.net/rest/api/';

    /**
     * @var \GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * @var \CoreBundle\Monolog\Channels\logger $logger
     */
    protected $logger;

    /**
     * @var string $product
     */
    protected $product;

    /**
     * @param \Slim\Container $container
     */
    public static function create($container)
    {

        $user = $container->get('parameters')['jira.user'];
        $apikey = $container->get('parameters')['jira.apikey'];
        $product = $container->get('settings')['product'] ?? '';

        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'base_uri' => self::WEBSERVICEROOT,
            'auth' =>
                [
                    $user,
                    $apikey,
                ]
        ]);

        return new static($client, $container->get('logger'), $product);
    }

    /**
     * Parameters injected via create()
     *
     * @param \GuzzleHttp\Client $client
     * @param string $logger Monolog logger object
     * @param string $product ID of the current product
     */
    public function __construct($client, $logger, $product)
    {
        parent::__construct($client, $logger, $product);
        $this->client = $client;
        $this->logger = $logger;
        $this->product = $product;
    }

    /**
     * Create a simple ticket by providing values
     *
     * @param string $projectId JIRA Project ID. From /rest/api/2/project/PROJECTNAME
     * @param string $issueTypeId Issue Type ID. From /rest/api/2/project/PROJECTNAME
     * @param string $title Ticket Title
     * @param array $contentParagraphs Ticket Content.
     *
     * Each element of the array is considered to be a separate
     * Node Element as described here
     * https://developer.atlassian.com/cloud/jira/platform/apis/document/structure/
     *
     * If no ElementType is specified, defaults to paragraph
     *
     * Supported node types: Paragraph, Panel
     *
     * @param array $fields Array containing "fields" properties to be overwritten / added
     * 
     * Fields example: ["customfield_1234" => "Example",...]
     * https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issues/#api-rest-api-3-issue-post
     *
     * @return array
     */
    public function createTicket(
        string $projectId,
        string $issueTypeId,
        string $title,
        array $contentParagraphs,
        array $fields = []
    ): array {

        $content = $this->arrayToAtlassianDocFormat($contentParagraphs);

        $data  = [
            "fields" => [
                "description" => [
                    "content" => $content,
                    "type" => "doc",
                    "version" => 1
                ],
                "project" => [
                    "id" => $projectId,
                ],
                "issuetype" => [
                "id" => $issueTypeId,
                ],
                "summary" => $title
            ]
        ];

        // Append /replace custom fields
        if (count($fields)) {
            $data["fields"] = array_replace_recursive($data["fields"],$fields);
        }

        try {
            $ticket = $this->createTicketRaw($data);

            return [
                'status' => 'success',
                'message' => 'Ticket Created',
                'data' => $ticket,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failure',
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Create a ticket using a raw JSON Array
     *
     * @param array $data JSON array of data for ticket to be created
     * More details here:
     * https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issues/#api-rest-api-3-issue-post
     *
     * @return \Psr\Http\Message\ResponseInterface;
     */
    public function createTicketRaw($data)
    {
        $endpoint = '3/issue';
        try {
            $response = $this->request(
                'POST',
                $endpoint,
                [
                    'body' => json_encode($data),
                ]
            );

            $body = $response->getBody()->getContents();

            return json_decode($body, true);
        } catch (\Exception $e) {
            $this->logger->info('JIRA.TICKET.CREATE', [
                'status_code' => 'NOT OK',
                'request' => $data,
                'others' => [
                    'exception' => $e->getMessage(),
                    'endpoint' => $endpoint,
                ],
            ]);
            $this->logException(
                'JIRA.TICKET.CREATE',
                self::WEBSERVICEROOT . $endpoint,
                $e
            );
            throw $e;
        }
    }

    /**
     * Fetch the ticket details
     *
     * @param string $ticketId The key or internal jira ID of the ticket
     */
    public function getTicket($ticketId)
    {

        $response =  $this->request(
            'GET',
            '3/issue/' . $ticketId
        );

        $data = $response->getBody()->getContents();

        return json_decode($data, true);
    }


    /**
     * Converts array to Atlassian Document Format
     * Each element of the array becomes a paragraph
     *
     * More details here:
     * https://developer.atlassian.com/cloud/jira/platform/apis/document/structure/
     *
     * @param array $content
     */
    private function arrayToAtlassianDocFormat($content)
    {
        return array_map(
            function ($element) {
                if (is_string($element)) {
                    $element = [
                        "type" => 'paragraph',
                        "content" => $element
                    ];
                }

                if (!is_array($element)) {
                    throw new \Exception('Invalid Content');
                }

                switch ($element['type'] ?? 'paragraph') {
                    case 'paragraph':
                        return $this->atlassianDocFormatParagraphFormatter($element['content']);
                        break;
                    case 'panel':
                        return $this->atlassianDocFormatPanelFormatter($element);
                        break;
                    default:
                        throw new \Exception('Node type not implemented');
                        break;
                }
            },
            $content
        );
    }

    /**
     * Handles paragraph formatting
     * If a string is passed, it is assumed to be the content of the paragraph.
     * If an array is passed, it is assumed the user passed the full structure of the paragraph
     *
     * More details here: https://developer.atlassian.com/cloud/jira/platform/apis/document/nodes/text/
     *
     * @param string|array $paragraph
     */
    private function atlassianDocFormatParagraphFormatter($paragraph) : array
    {

        $content = [];

        if (is_string($paragraph)) {
            $content = [
                [
                    "type" => "text",
                    "text" => $paragraph
                ]
            ];
        }

        // Advanced use, assuming user passed full paragraph structure
        if (is_array($paragraph)) {
            $content = $paragraph;
        }

        return [
            "content" => $content,
            "type" => "paragraph"
        ];
    }

    /**
     * Generates a panel structure
     *
     * More info here: https://developer.atlassian.com/cloud/jira/platform/apis/document/nodes/panel/
     *
     * @param array $panel The panel structure
     * Example panel
     * [
     *  'type' = 'panel',
     *  'panelType' = 'warning',
     *  'content' = 'Test',
     * ]
     *
     * Content is a regular Paragraph structure as described in atlassianDocFormatParagraphFormatter
     *
     * @return array
     */
    private function atlassianDocFormatPanelFormatter(array $panel): array
    {
        return [
            'content' => [$this->atlassianDocFormatParagraphFormatter($panel['content'])],
            'type' => 'panel',
            'attrs' => [
                'panelType' => $panel['panelType'] ?? 'info',
            ],
        ];
    }

    /**
     * Creats an atlassian doc format paragraph containing
     * a single text element linking to $link
     *
     * @param string $link
     * @param string $text The content of the link
     *
     * @return array
     *
     */
    public function atlassianDocFormatLinkFormatter(string $link, string $text): array
    {
        $ret = [
            "content" =>
            [
                [
                    'type' => "text",
                    "text" => $text
                ],
            ],
            "type" => "paragraph"
        ];

        if ($link !== '') {
            $ret['content'][0]["marks" ] =
                [
                    [
                        "type" => "link",
                        "attrs" => [
                            "href" => $link,
                            "title" => $text
                        ]
                    ]
                ];
        }

        return $ret;
    }
}

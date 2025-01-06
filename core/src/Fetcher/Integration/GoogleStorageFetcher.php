<?php

namespace App\Fetcher\Integration;

use App\Fetcher\LogTrait;

/**
 *
 */
class GoogleStorageFetcher
{
    use LogTrait;

    private $logger;

    private $googleService;

    /**
     * @param \Slim\Container $container
     */
    public static function create($container)
    {
        $authFile = $container->get('parameters')['google.service.key'];

        $googleClient = new \Google_Client();
        $googleClient->setAuthConfig($authFile);
        $googleClient->addScope(\Google_Service_Drive::DRIVE);
        $googleClient->setAccessType('offline');

        $googleService = new \Google_Service_Drive($googleClient);

        return new static($googleService, $container->get('logger'));
    }


    public function __construct($googleService, $logger)
    {
        $this->googleService = $googleService;
        $this->logger = $logger;
    }

    /**
     * Stores uploads using Google Service Account on specified Google Drive Folder.
     * @param string $folderId Google Drive Folder ID where files will be uploaded
     * 
     * @param string $sourceFilePath File path to be uploaded
     * 
     * @param string $targetFilename File name of file to be uploaded
     * 
     * @param string $fileType File type of file to be uploaded
     * 
     * @return string Google Drive Web link where file was uploaded
     */
    public function storeUsingServiceAccount($folderId, $sourceFilePath, $targetFilename, $fileType)
    {
        try {
            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $targetFilename,
                'parents'    => [$folderId]
            ]);

            $result = $this->googleService->files->create($fileMetadata, [
                'data'       => file_get_contents($sourceFilePath),
                'mimeType'   => $fileType,
                'uploadType' => 'multipart',
                'fields' => 'webViewLink'
            ]);

            return [
                'status' => 'success',
                'message' => 'File uploaded',
                'data' => $result->webViewLink
            ];

        } catch (\Exception $e) {
            $this->logger->error('DOCUMENT.UPLOADTO.DRIVE', [
                'status_code' => 'NOT OK',
                'request' => $fileMetadata,
                'others' => [
                    'exception' => $e->getMessage(),
                ],
            ]);

            return [
                'status' => 'failure',
                'message' => 'File failed to upload',
                'data' => []
            ];
        }
   }
}

<?php

namespace App\Factory;

class GoogleCloudStorageServiceFactory
{
    public static function createGoogleCloudStorage()
    {
      
        $keyFileLocation = __DIR__ . '../../../config/keys/projet-test-380216-c47f4b612095.json';
        $bucketName = 'fredgruwedev';
        $bucketLocation = 'EUROPE-WEST9';

        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $keyFileLocation);
        $client = new \Google\Client();
        $client->setApplicationName('profile_photos');
        $client->addScope(\Google\Service\Storage::DEVSTORAGE_FULL_CONTROL);
        $client->useApplicationDefaultCredentials();

        return new \Google\Service\Storage($client);
    }
}
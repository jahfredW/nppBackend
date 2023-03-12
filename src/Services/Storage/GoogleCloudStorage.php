<?php

namespace App\Services\Storage;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

/**
 * This class is responsible to return a new Google Cloud Storage bucket instance.
 */
class GoogleCloudStorage {

    /**
     * This method is auto executed from the FlySystem when initializing the Google Cloud Storage.
     *
     * @param string $bucket The bucket name.
     *
     * @return Bucket The bucket instance.
     */
    public function bucket( string $bucket = '' ) : Bucket {
        $keyFile = [
            'type'                        => $_SERVER['GCS_TYPE'] ?? '',
            'project_id'                  => $_SERVER['GCS_PROJECT_ID'] ?? '',
            'private_key_id'              => $_SERVER['GCS_PRIVATE_KEY_ID'] ?? '',
            'private_key'                 => $_SERVER['GCS_PRIVATE_KEY'] ?? '',
            'client_email'                => $_SERVER['GCS_CLIENT_EMAIL'] ?? '',
            'client_id'                   => $_SERVER['GCS_CLIENT_ID'] ?? '',
            'auth_uri'                    => $_SERVER['GCS_AUTH_URI'] ?? '',
            'token_uri'                   => $_SERVER['GCS_TOKEN_URI'] ?? '',
            'auth_provider_x509_cert_url' => $_SERVER['GCS_AUTH_PROVIDER_X509_CERT_URL'] ?? '',
            'client_x509_cert_url'        => $_SERVER['GCS_CLIENT_X509_CERT_URL'] ?? '',
        ];

        $client = new StorageClient(
            [
                'projectId' => $keyFile['project_id'],
                'keyFile'   => $keyFile,
            ]
        );

      

        return $client->bucket( $bucket );
    }

}
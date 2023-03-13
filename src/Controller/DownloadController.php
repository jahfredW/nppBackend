<?php

namespace App\Controller;


use GuzzleHttp\Psr7\Stream;
use Google\Cloud\Storage\StorageClient;
use App\Services\Storage\GoogleCloudStorage;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Factory\GoogleCloudStorageServiceFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class DownloadController extends AbstractController {

    #[Route('/api/downloadFile', name: 'download', methods: ['GET'])]
    public function upload(Request $request)
    {

    $bucketName = 'fredgruwedev';
    $objectName = '2681499bf68c388ca6ea2355b03f85ea.jpg';
    
    $storage = GoogleCloudStorage::getInstance($bucketName);
    $storage->setObjectName($objectName);
   
    
    // $object = GoogleCloudStorage::bucket($bucketName)->object($objectName);

    $object = $stream = $storage->getObject();

    // $validityDuration = 300; // 5 minutes

    // $url = $object->signedUrl(new \DateTime('+'.$validityDuration.' seconds'));

    // $context = stream_context_create([
    //     'gs' => [
    //         'key' => file_get_contents($url),
    //     ],
    // ]);

    $stream = $storage->getObject()->downloadAsStream();

    // $stream = fopen('gs://my-bucket/path/to/my/object', 'r', false, $context);
   
   
    $response = new StreamedResponse();

    $response->setCallback(function() use ($stream) {
        fpassthru($stream->detach());
    });

    $response->headers->set('Content-Type', 'image/jpeg');
    $response->headers->set('Content-Disposition', 'attachment; filename="2681499bf68c388ca6ea2355b03f85ea.jpg"');
   
    

    return $response;

    }

    #[Route('/api/downloadLink', name: 'downloadLink', methods: ['GET'])]
    public function uploaded(Request $request)
    {
    
    
    // Get the bucket name and object name
    $bucketName = 'fredgruwedev';
    $objectName = '2681499bf68c388ca6ea2355b03f85ea.jpg';

    $storage = GoogleCloudStorage::getInstance($bucketName);
    $storage->setObjectName($objectName);

    // Create a signed URL with a short expiration time (5 minutes)
    // Create a signed URL with a short expiration time (5 minutes)
    $content = $storage->getObject()
         ->signedUrl(new \DateTime('+5 minutes'), [
             'version' => 'v4',
             'private' => true,
         ]);


         return new Response($content);

    


    }

   
}

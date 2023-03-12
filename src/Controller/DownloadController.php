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
    public function upload(Request $request, GoogleCloudStorage $storage)
    {
    

    // Get the bucket name and object name
    $bucketName = 'fredgruwedev';
    $objectName = '2681499bf68c388ca6ea2355b03f85ea.jpg';

    $object = $storage->bucket($bucketName)->object($objectName);
    $stream = $object->downloadAsStream();
   
   
    $response = new StreamedResponse();

    $response->setCallback(function() use ($stream) {
        fpassthru($stream->detach());
    });

    // $response->headers->set('Content-Type', 'image/jpeg');
    // $response->headers->set('Content-Disposition', 'attachment; filename="2681499bf68c388ca6ea2355b03f85ea.jpg"');
   
    


    return $response;




    // Create a signed URL with a short expiration time (5 minutes)
    // $content = $storage->bucket($bucketName)->object($objectName)
    //     ->signedUrl(new \DateTime('+5 minutes'), [
    //         'version' => 'v4',
    //         'private' => true,
    //     ]);

    //     return new Response($content);
    }

    // #[Route('/api/downloadFile', name: 'download', methods: ['GET'])]
    // public function uploaded(Request $request, GoogleCloudStorage $storage)
    // {
    

    // Get the bucket name and object name
    // $bucketName = 'fredgruwedev';
    // $objectName = '2681499bf68c388ca6ea2355b03f85ea.jpg';

    // // Create a signed URL with a short expiration time (5 minutes)
    // $storageObject = $storage->bucket($bucketName)->object($objectName);
    // $content = $storageObject->downloadAsStream();

    



        

    //     $response = new StreamedResponse(function () use ($content) {
    //         $content;
    //     });
    //     // $response->headers->set('Content-Type', 'image/jpeg');
    //     // $response->headers->set('Content-Disposition', 'attachment; filename="test"');
    //     return $response;
    // }

   
}

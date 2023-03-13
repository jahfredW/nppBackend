<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UploadController extends AbstractController
{
    private $em;
    Const MAX_FILE_SIZE = 10000000;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    #[Route('/api/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $files = $request->files->get('files');

        if (empty($files)) {
            throw new \Exception('No file was uploaded.');
        }


        $successUpload = [];
        $failureUpload = [];
        
        $extensions = ['jpg', 'png', 'gif', 'png'];


        $directory = __DIR__ . '/../../public/uploads';

        if(!file_exists($directory)){
            mkdir($directory, 0777, true);
        }

        foreach($files as $file){

            if(!in_array($file->guessExtension(), $extensions) || $file->getSize() > self::MAX_FILE_SIZE ){
                $failureUpload[] = $file;

                return new JsonResponse('echec, un fichier n\'est pas valide en taille ou en extension', Response::HTTP_UNAUTHORIZED);
            } 

            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($directory, $filename);
        }
        
        return new JsonResponse('Les fichiers ont bien été sauvegardés', Response::HTTP_CREATED);
    }
}

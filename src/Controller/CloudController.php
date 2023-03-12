<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Google\Cloud\Storage\StorageClient;


class CloudController extends AbstractController
{
    private $filesystemMap;

    public function __construct(FilesystemMap $filesystemMap)
    {
        $this->filesystemMap = $filesystemMap;
    }

    #[Route('/api/cloud', name: 'cloud', methods: ['POST'])]
    public function upload(Request $request)
    {
        // récupère les fichiers à uploader
        $files = $request->files->get('files');
       
        if (empty($files)) {
            throw new \Exception('No file was uploaded.');
        }

        

        $filenames = [];
        // boucle sur les fichiers et stocke chaque fichier dans Gaufrette
        foreach ($files as $file) {
            // crée un nom de fichier unique
            $filename = uniqid() . '.' . $file->guessExtension();
            dd($file->guessExtension());
            // récupère le système de fichier pour le stockage
            $filesystem = $this->filesystemMap->get('profile_photos');
            // stocke le fichier
            $filesystem->write($filename, file_get_contents($file->getPathname()));
            // ajoute le nom de fichier à la liste des noms de fichiers
            $filenames[] = $filename;
        }

        // renvoie une réponse de succès avec les noms de fichiers et les URL signées pour accéder aux fichiers
        $urls = [];
        foreach ($filenames as $filename) {
            $url = $this->generateUrl('file_download', ['filename' => $filename], true);
            $urls[] = $url;
        }
        return new JsonResponse(['success' => true, 'filenames' => $filenames, 'urls' => $urls]);
    }
}

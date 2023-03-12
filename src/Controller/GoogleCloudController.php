<?php



namespace App\Controller;


use App\Services\Storage\GoogleCloudStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Factory\GoogleCloudStorageServiceFactory;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Google\Cloud\Storage\StorageClient;


class GoogleCloudController extends AbstractController
{

    #[Route('/api/cloudgoogle', name: 'cloud', methods: ['POST'])]
    public function upload(Request $request, GoogleCloudStorage $storage)
    {
        
        // récupère les fichiers à uploader
        $files = $request->files->get('files');
        $successUpload = [];
        $failureUpload = [];
        

        $extensions = ['jpg', 'png', 'gif', 'png'];

        $bucket = $storage->bucket('fredgruwedev');

        if (empty($files)) {
            throw new \Exception('No file was uploaded.');
        }

        // $filenames = [];
        // boucle sur les fichiers et stocke chaque fichier dans Gaufrette
        foreach ($files as $file) {
            // crée un nom de fichier unique
            if(!in_array($file->guessExtension(), $extensions) || $file->getSize() > 100000000 ){
                $failureUpload[] = $file;

                return new JsonResponse('echec, un fichier n\'est pas valide en taille ou en extension');
            } 

            
            // stocke le fichier
            // $object = $bucket->upload($file , ['name' => 'test']);
            
            // ajoute le nom de fichier à la liste des noms de fichiers
            // $filenames[] = $filename;
            //  else {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $object = $bucket->upload(fopen($file, 'r'), [
                    'predefinedAcl' => 'private',
                    'name' => $filename
                ]);
                // $successUpload[] = [$file, $filename];
            // } ;
            
        }
       
        // foreach($successUpload as $file){
        //     $object = $bucket->upload(fopen($file[0], 'r'), [
        //         'predefinedAcl' => 'publicRead',
        //         'name' => $file[1]
        //     ]);
        // }
        

        // renvoie une réponse de succès avec les noms de fichiers et les URL signées pour accéder aux fichiers
        // $urls = [];
        // foreach ($filenames as $filename) {
        //     $url = $this->generateUrl('file_download', ['filename' => $filename], true);
        //     $urls[] = $url;
        // }
        return new JsonResponse(['photos uploadées avec succès']);
    }
}

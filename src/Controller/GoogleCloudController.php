<?php



namespace App\Controller;


use App\Entity\Picture;
use App\Entity\Session;
use Google\Cloud\Storage\StorageClient;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Storage\GoogleCloudStorage;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Factory\GoogleCloudStorageServiceFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class GoogleCloudController extends AbstractController
{
    private $em;
    const BUCKET_NAME = 'fredgruwedev';

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }
    #[Route('/api/cloudgoogle', name: 'cloud', methods: ['POST'])]
    public function upload(Request $request)
    {
        
        // récupère les fichiers à uploader
        $files = $request->files->get('files');
        // récupère le nom de la session 
        $sessionName = $request->get('session');
        // récupère l'entité session correspondante 
        $session = $this->em->getRepository(Session::class)->findOneByName($sessionName);
        
        // Détermine si c'est une session du matin ou non 
        $matin = $request->get('matin');
      
        // Initialisation du tableau d'extensions
        $extensions = ['jpg', 'png', 'gif', 'png'];

        // initialisation du nom de bucket 
        $bucketName = GoogleCloudController::BUCKET_NAME;

        // Récupération d'une instance de GoogleCloudStorage ( singleton )
        $storage = GoogleCloudStorage::getInstance($bucketName);

        // récupération du bucket
        $bucket = $storage->getClient();

        if (empty($files)) {
            throw new \Exception('No file was uploaded.');
        }

     
        // boucle sur les fichiers et stocke chaque fichier dans Gaufrette
        foreach ($files as $file) {
            
            if(!in_array($file->guessExtension(), $extensions) || $file->getSize() > 100000000 ){
                $failureUpload[] = $file;

                return new JsonResponse('echec, un fichier n\'est pas valide en taille ou en extension');
            } 

                // crée un nom de fichier unique
                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                // récupération du nom original 
                $originalName = strtolower(explode('.',$file->getClientOriginalName())[0]);
                
                // instanciaiton d'un nouvel objet picture et setting
                $picture = new Picture();

                $picture->setName($originalName);
                $picture->setFileName($filename);
             
                $picture->setMorning($matin);
                // $picture->setCreatedAt(new \DateTimeImmutable());
                $picture->setSession($session);

                // upload en mode privé 
                $object = $bucket->upload(fopen($file, 'r'), [
                    'predefinedAcl' => 'private',
                    'name' => $filename
                ]);
                
                // inscription en bdd
                $this->em->persist($picture);
                $this->em->flush();


            
        }
       
        // renvoie une réponse de succès avec les noms de fichiers et les URL signées pour accéder aux fichiers
        // $urls = [];
        // foreach ($filenames as $filename) {
        //     $url = $this->generateUrl('file_download', ['filename' => $filename], true);
        //     $urls[] = $url;
        // }
        return new JsonResponse(['photos uploadées avec succès']);
    }
}

<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Director;
use App\Repository\FilmRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\SerializerInterface;
// use JMS\Serializer\SerializationContext;
// use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FilmController extends AbstractController
{
    private $filmRepository;
    private $serializer;
    private $em;

    public function __construct(FilmRepository $filmRepository, SerializerInterface $serializer, 
    EntityManagerInterface $em){
        $this->filmRepository = $filmRepository;
        $this->serializer = $serializer;
        $this->em = $em;
    }
    // Read all films 
    #[Route('/api/films', name: 'app_film', methods: ['GET'])]
    // #[IsGranted('ROLE_USER', message: " vous n'avez pas les droits")]
    public function getFilmList(FilmRepository $filmRepository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);

        // on compte le nombre de films en base de données
        $filmListLength = $this->filmRepository->countAllFilms();

        // on récupère la dernière page
        $pageMaxNum = (int)round($filmListLength[1] / $limit);

        // Si la dernière page est égale à la page passée en paramètre, alors on retourne une Json No Content
        // Evite de mettre en cache une page vide. 
        if($page === $pageMaxNum){
            return new JsonResponse(
                null, Response::HTTP_NO_CONTENT, [], JSON_PRETTY_PRINT
            );
        }

        
        // système de mis en cache 
        $idCache = "getFilmList-" . $page . "-" . $limit;

        $filmList = $cachePool->get($idCache, function(ItemInterface $item) use ($page, $limit) {
            $item->tag('filmsCache');
            return $this->filmRepository->findAllWithPagination($page, $limit);
        });
            
        $jsonFilmList = $this->serializer->serialize($filmList, 'json', ['groups' => 'getFilms']);
       
        return new JsonResponse(
            $jsonFilmList, Response::HTTP_OK, [], true
        );
     
        
    }

    #[Route('api/films/count', name: 'all_film', methods: ['GET'])]
    public function countFilms(FilmRepository $filmRepository) : JsonResponse
    {
        $filmListLength = $this->filmRepository->countAllFilms();
        $pageMaxNum = (int)round($filmListLength[1] / 5);

        return new JsonResponse($pageMaxNum, Response::HTTP_OK, [], true);
    }

    // Read One film 
    #[Route('api/films/{id}', name: 'detail_film', methods: ['GET'])]
    public function getFilmBySensio($id, Film $film) : JsonResponse
    {
        // $film = $this->filmRepository->find($id);
        // if ($film){
            $jsonFilm = $this->serializer->serialize($film, 'json', ['groups' => 'getFilms']);
            return new JsonResponse(
                $jsonFilm, Response::HTTP_OK, [], true
            );
        // }
        // return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }



    // Delete one film 
    #[Route('api/films/{id}', name: 'delete_film', methods: ['DELETE'])]
    public function deleteFilm($id, Film $film) : JsonResponse
    {
        // $film = $this->filmRepository->find($id);
        // if ($film){
            
            $this->em->remove($film);
            $this->em->flush();

            return new JsonResponse(
                null, Response::HTTP_NO_CONTENT
            );
        // }
        // return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Create One film 
    #[Route('api/films', name: 'add_film', methods: ['POST'])]
    public function AddFilm(Request $request, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator ) : JsonResponse
    {

            
            // récupération des datas post
            // $post_data = json_decode($request->getContent(), true);
            // // test si le nom de l'auteur est défini 
            // if(isset($post_data['director']['lastName'])){
            //     $lastName = $post_data['director']['lastName'];
            //     // on récupère l'entité director correspondante
            //     $director = $this->em->getRepository(Director::class)->findOneByLastName($lastName);
            //     // Le firstname du director est facultatif ici 
            //     $firstName = $post_data['director']['firstName'] ?? -1;
                
            //     // Création d'une nouvelle entité Film 
            //     $film = new Film();

            //     // Si le titre du film est défini et qu'il n'est pas vide
            //     if(isset($post_data['title']) && !empty($post_data['title'])){
            //         // on bind les props avec les posts datas 
            //         $film->setTitle($post_data['title']);
            //         $film->setDescription($post_data['description']);
            //         // Si le director existe déja, on le bind 
            //         if($director){
            //             $film->setDirector($director);
            //         } else {
            //             // sinon on en crée un nouveau 
            //             $director = new Director();
            //             $director->setLastName($lastName);
            //             if($firstName) {
            //                 $director->setFirstName($firstName);
            //             }
            //             // on sauvegarde le director en bdd 
            //             $this->em->getRepository(Director::class)->save($director, true);
            //             // on attribue le director au film 
            //             $film->setDirector($director);
            //        }
                    $film = $this->serializer->deserialize($request->getContent(), Film::class, 'json');

                    $errors = $validator->validate($film);
                    if($errors->count() > 0) {
                        return new jsonResponse($this->serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST,[], true);
                    }

                    if($film->getDirector()->getLastName() === null ){
                        return new JsonResponse('le titre ne peut être vide', Response::HTTP_FORBIDDEN);
                    } 
                        
                    $content = $request->toArray();
             
                    $directorLastName = $content['director']['lastName'];
                    if(isset($content['director']['firstName'])){
                        $directorFirstName = $content['director']['firstName'];
                    }
                    $director = $this->em->getRepository(Director::class)->findOneByLastName($directorLastName); 
                    if($director){
                        $film->setDirector($director);
                    } else {
                        $director = new Director();
                        $director->setLastName($directorLastName);
                    }
                    if($directorFirstName) {
                        $director->setFirstName($directorFirstName);
                        }

                    $this->em->getRepository(Director::class)->save($director, true);
                    $film->setDirector($director);
                    
                    $this->em->persist($film);
                    $this->em->flush();
                    
                    // generation de l'url de localisation dans le header de la réponse 
                    $location = $urlGenerator->generate('detail_film', ['id' => $film->getId()], 
                    UrlGeneratorInterface::ABSOLUTE_URL);
                    
                    // réponse HTTP 
                    return new JsonResponse('ok', Response::HTTP_CREATED, ["location" => $location], true);


            //     //} else {
            //         return new JsonResponse('le titre ne peut être vide', Response::HTTP_FORBIDDEN);
            //     };
            
            // } else {
            //     return new JsonResponse('Vous devez entrer un auteur', Response::HTTP_FORBIDDEN);
            // }
            

    }

    // update One Film 
    #[Route("/api/films/{id}",name: "film_update", methods:['PUT'])]
    public function updateFilm($id, Film $film, Request $request, ValidatorInterface $validator ){

    $updatedFilm = $this->serializer->deserialize($request->getContent(), 
    Film::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $film]);

    $errors = $validator->validate($updatedFilm);
    
    $content = $request->toArray();
    if(isset($content['title']) && !empty($content['title'])){
        if(isset($content['director']['lastName'])){
            $newDirectorLastName = $content['director']['lastName'];
            if($director = $this->em->getRepository(Director::class)->findOneByLastName($newDirectorLastName)){
                $updatedFilm->setDirector($director);
            } else {
                $director = new Director();
                $director->setLastName($newDirectorLastName);
                if(isset($content['director']['firstName'])){
                    $director->setFirstName($content['director']['firstName']);
                    
                }
                $updatedFilm->setDirector($director);
                $this->em->getRepository(Director::class)->save($director, true); 
                
            };
            $this->em->persist($film);
            $this->em->flush();
        }
    
    } else {
        throw new HttpException(404, "Ceci est un test");
    }
        

    return new JsonResponse("updated", Response::HTTP_CREATED);
    }


    
}

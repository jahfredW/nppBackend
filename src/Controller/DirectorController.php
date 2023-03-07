<?php

namespace App\Controller;

use App\Entity\Director;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DirectorController extends AbstractController
{
    private $em;
    private $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer){
        $this->em = $em;
        $this->serializer = $serializer;
    }

    // Read All Directors
    #[Route('/api/directors', name: 'app_director', methods: ['GET'])]
    public function getAllDirectors(): JsonResponse
    {
        $directorRepo = $this->em->getRepository(Director::class);
        $directorList = $directorRepo->findAll();
   
        $jsonDirectorList = $this->serializer->serialize($directorList, 'json', ['groups' => 'getDirectors']);
  
        return new JsonResponse(
            $jsonDirectorList, Response::HTTP_OK, [], true
        );
    }

    // Read One director 
    #[Route('/api/directors/{id}', name: 'detail_director', methods: ['GET'])]
    public function getDirectorById($id, Director $director): JsonResponse
    {
        $jsonDirector = $this->serializer->serialize($director, 'json', ['groups' => 'getDirectors']);

        return new JsonResponse($jsonDirector, Response::HTTP_OK, [], true);
    }

    // Delete One Director 
    #[Route('/api/directors/{id}', name: 'delete_director', methods: ['DELETE'])]
    public function deleteDirectorById($id, Director $director) : JsonResponse
    {
        $this->em->remove($director);
        $this->em->flush();

        return new JsonResponse('done', Response::HTTP_NO_CONTENT, [], true);
    }

    // Post One Director
    #[Route('/api/directors', name: 'post_director', methods: ['POST'])]
    public function addDirector(Request $request, UrlGeneratorInterface $urlGenerator) : JsonResponse
    {
        $director = $this->serializer->deserialize($request->getContent(), Director::class , 'json');
        $this->em->persist($director);
        $this->em->flush();

        $jsonDirector = $this->serializer->serialize($director, 'json', ['groups' => 'getDirectors']);

        $location = $urlGenerator->generate('detail_director', ['id' => $director->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonDirector, Response::HTTP_CREATED, ['location' => $location], true);
    }

    // Update a Director
    #[Route('/api/directors/{id}', name: 'update_direct', methods: ['PUT'])]
    public function updatedDirector($id, Director $director, Request $request)
    {
        $updatedDirector = $this->serializer->deserialize($request->getContent(), 
        Director::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $director]);
        dd($updatedDirector);

        $content = $request->toArray();

        if(isset($content['films']) && !empty($content['films'])){
            $updatedDirector->setFilms($content['films']);
        }

        $this->em->persist($updatedDirector);
        $this->em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SignupController extends AbstractController
{
    private $em;
    private $hasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $hasher )
    {
        $this->em = $em;
        $this->hasher = $hasher;
    }

    #[Route('/api/signup', name: 'app_signup', methods: ['POST'])]

    public function index(Request $request, ValidatorInterface $validator ) : JsonResponse
    {
        $user = new User();
        // $post_data = $request->request->all();
        $post_data = json_decode($request->getContent(), true);
        if($post_data){
            $pseudo = $post_data['pseudo'];
            $email = $post_data['email'];
            $password = $post_data['password'];
            $verification = $post_data['verification'];
        

            if ($password != $verification){
                throw new HttpException(404, "Ton password n'est pas le même blaireau");


            } else {
                $user->setPseudo($pseudo);
                $user->setEmail($email);
                $user->setPassword($this->hasher->hashPassword($user, $password));

                $errors = $validator->validate($user);

                if( count($errors) > 0 ) {
                    $errorsString = (string) $errors;

                    throw new HttpException(403, " T'as voulu nous baiser?? ");
            }
        }  
        }
        
        $this->em->persist($user);
        $this->em->flush();

       
        return new JsonResponse([
            'data' => ['email' => $user->getEmail()],
        ]);
    }
}
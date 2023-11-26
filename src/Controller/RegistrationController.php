<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{

    public function __construct(UserRepository $userRepository)
    {
        $this->user=$userRepository;
    }


    
    #[Route('/api/register', name: 'app_register', methods:'POST')]
    public function index(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
    

        $em = $doctrine->getManager();
        $decoded = json_decode($request->getContent());
        $email = $decoded->email;
        $plaintextPassword = $decoded->password;

        $checkEmail=$this->user->findOneByEmail($email);

        if($checkEmail){
            return new JsonResponse([
                "statut"=>false,
                "message"=>"Cet email existe déjà, vous devez choisir un autre !"
            ]);

        }else{
      
            $user = new User();
    
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            $user->setEmail($email);

             // Vérifiez si le champ "roles" est présent dans les données de la requête
        if (property_exists($decoded, 'roles')) {
            // Assurez-vous que les rôles sont un tableau
            if (is_array($decoded->roles)) {
                $user->setRoles($decoded->roles);
            } else {
                $user->setRoles([$decoded->roles]);
            }
        } 
        // else {
        //     // Si le champ "roles" n'est pas présent, attribuez un rôle par défaut
        //     $user->setRoles(['ROLE_USER']);
        // }

        $em->persist($user);
        $em->flush();


            // $user->setRoles(['ROLE_USER']);
    
            // $em->persist($user);
            // $em->flush();
      
            return $this->json(['message' => 'Enregistré avec succès']);
        }


    }
}

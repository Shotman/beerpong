<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_SUPER_ADMIN")]
final class AdminController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(RequestStack $requestStack, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            "users" =>$users
        ]);
    }

    #[Route('/admin/user-modal/{user}', name: 'app_admin_user_modal', methods: ["GET", "POST","DELETE"])]
    function getModal(RequestStack $requestStack, ?User $user = null): Response
    {
        $deleteResponse = $this->handleDeleteRequest($requestStack, $user);
        if($deleteResponse !== false){
            return $deleteResponse;
        }

        $formUrlOptions = [];
        if(!is_null($user)){
            $formUrlOptions = ['user' => $user->getId()];
        }
        $form = $this->createForm(UserType::class, $user, [
            'attr' => [
                'id' => 'user-edit-form',
                'hx-target' => '#userModal',
                "hx-swap" => 'innerHTML',
                "hx-trigger" => 'submit',
                "hx-post" => $this->generateUrl('app_admin_user_modal', $formUrlOptions),
            ],
        ]);

        $formResponse = $this->handleFormSubmit($requestStack, $form);
        if($formResponse !== false){
            return $formResponse;
        }

        $response = new Response();
        $response->headers->set('HX-Trigger-After-Swap', json_encode([
            'hx-showModal' => '#userModal',
        ]));

        return $this->render('admin/user-modal.html.twig', [
            'form' => $form->createView(),
        ], $response);
    }

    private function handleFormSubmit($requestStack , $form): Response|false {
        if($requestStack->getCurrentRequest()->isMethod('POST')) {
            $form->handleRequest($requestStack->getCurrentRequest());
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $newPassword = $form->get('newPassword')->getData();
                if (!is_null($newPassword)) {
                    $user->setPassword(
                        password_hash($newPassword, PASSWORD_BCRYPT)
                    );
                }
                $entityManager = $this->entityManager;
                $userRepository = $entityManager->getRepository(User::class);
                $entityManager->persist($user);
                $entityManager->flush();
                $response = new Response($this->renderBlockView("admin/index.html.twig","userTable",[
                    "users" => $userRepository->findAll(),
                ]));
                $response->headers->set("HX-Trigger", json_encode([
                    "hx-hideModal" => "#userModal"
                ]));
                $response->headers->set('HX-Retarget', "#usersTable");
                return $response;
            }
        }
        return false;
    }

    private function handleDeleteRequest(RequestStack $requestStack, ?User $user): Response|false
    {
        if($requestStack->getCurrentRequest()->getMethod() === 'DELETE'){
            $entityManager = $this->entityManager;
            $userRepository = $entityManager->getRepository(User::class);
            if($user !== null){
                $userRepository->remove($user, true);
            }
            $response = new Response($this->renderBlockView("admin/index.html.twig","userTable",[
                "users" => $userRepository->findAll(),
            ]));
            $response->headers->set('HX-Retarget', "#usersTable");
            return $response;
        }
        return false;
    }
}

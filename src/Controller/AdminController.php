<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CategoryType;
use App\Form\StatusType;
use App\Form\UserType;
use App\Form\AdminTicketType;
use App\Repository\CategoryRepository;
use App\Repository\StatusRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        TicketRepository $ticketRepository,
        CategoryRepository $categoryRepository,
        StatusRepository $statusRepository,
        UserRepository $userRepository
    ): Response {
        $stats = [
            'total_tickets' => $ticketRepository->count([]),
            'open_tickets' => $ticketRepository->countByStatus(['Nouveau', 'Ouvert']),
            'total_users' => $userRepository->count([]),
            'total_categories' => $categoryRepository->count([]),
            'total_statuses' => $statusRepository->count([]),
        ];

        $recentTickets = $ticketRepository->findBy([], ['openedAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'recent_tickets' => $recentTickets,
        ]);
    }

    // CRUD Catégories
    #[Route('/categories', name: 'admin_categories')]
    public function categories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categories/new', name: 'admin_category_new')]
    public function newCategory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès !');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/categories/{id}/edit', name: 'admin_category_edit')]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès !');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/categories/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function deleteCategory(Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($category->getTickets()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette catégorie car elle est utilisée par des tickets.');
        } else {
            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        }

        return $this->redirectToRoute('admin_categories');
    }

    // CRUD Statuts
    #[Route('/statuses', name: 'admin_statuses')]
    public function statuses(StatusRepository $statusRepository): Response
    {
        $statuses = $statusRepository->findAll();
        
        return $this->render('admin/statuses/index.html.twig', [
            'statuses' => $statuses,
        ]);
    }

    #[Route('/statuses/new', name: 'admin_status_new')]
    public function newStatus(Request $request, EntityManagerInterface $entityManager): Response
    {
        $status = new Status();
        $form = $this->createForm(StatusType::class, $status);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($status);
            $entityManager->flush();

            $this->addFlash('success', 'Statut créé avec succès !');
            return $this->redirectToRoute('admin_statuses');
        }

        return $this->render('admin/statuses/new.html.twig', [
            'status' => $status,
            'form' => $form,
        ]);
    }

    #[Route('/statuses/{id}/edit', name: 'admin_status_edit')]
    public function editStatus(Status $status, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StatusType::class, $status);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Statut modifié avec succès !');
            return $this->redirectToRoute('admin_statuses');
        }

        return $this->render('admin/statuses/edit.html.twig', [
            'status' => $status,
            'form' => $form,
        ]);
    }

    #[Route('/statuses/{id}/delete', name: 'admin_status_delete', methods: ['POST'])]
    public function deleteStatus(Status $status, EntityManagerInterface $entityManager): Response
    {
        if ($status->getTickets()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer ce statut car il est utilisé par des tickets.');
        } else {
            $entityManager->remove($status);
            $entityManager->flush();
            $this->addFlash('success', 'Statut supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_statuses');
    }

    // CRUD Utilisateurs
    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        
        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/new', name: 'admin_user_new')]
    public function newUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si un nouveau mot de passe est fourni
            if ($form->get('plainPassword')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès !');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        if ($user->getAssignedTickets()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cet utilisateur car il est responsable de tickets.');
        } else {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_users');
    }

    // CRUD Tickets
    #[Route('/tickets', name: 'admin_tickets')]
    public function tickets(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findBy([], ['openedAt' => 'DESC']);
        
        return $this->render('admin/tickets/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/tickets/{id}', name: 'admin_ticket_show')]
    public function showTicket(Ticket $ticket): Response
    {
        return $this->render('admin/tickets/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/tickets/{id}/edit', name: 'admin_ticket_edit')]
    public function editTicket(Ticket $ticket, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminTicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le statut devient "Fermé", définir la date de fermeture
            if ($ticket->getStatus()->getName() === 'Fermé' && !$ticket->getClosedAt()) {
                $ticket->setClosedAt(new \DateTime());
            }

            $entityManager->flush();

            $this->addFlash('success', 'Ticket modifié avec succès !');
            return $this->redirectToRoute('admin_tickets');
        }

        return $this->render('admin/tickets/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }
}
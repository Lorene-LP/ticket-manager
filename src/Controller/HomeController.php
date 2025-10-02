<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\CategoryRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        StatusRepository $statusRepository
    ): Response {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir le statut par défaut "Nouveau"
            $defaultStatus = $statusRepository->findDefaultStatus();
            if ($defaultStatus) {
                $ticket->setStatus($defaultStatus);
            }

            $entityManager->persist($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Votre ticket a été créé avec succès ! Nous vous contacterons bientôt.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'ticketForm' => $form->createView(),
        ]);
    }
}
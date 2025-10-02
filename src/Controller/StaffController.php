<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/staff')]
#[IsGranted('ROLE_STAFF')]
class StaffController extends AbstractController
{
    #[Route('/', name: 'staff_dashboard')]
    public function dashboard(TicketRepository $ticketRepository): Response
    {
        $stats = [
            'total_tickets' => $ticketRepository->count([]),
            'open_tickets' => $ticketRepository->countByStatus(['Nouveau', 'Ouvert']),
            'my_tickets' => $ticketRepository->count(['responsible' => $this->getUser()]),
        ];

        $myTickets = $ticketRepository->findBy(['responsible' => $this->getUser()], ['openedAt' => 'DESC'], 5);
        $recentTickets = $ticketRepository->findBy([], ['openedAt' => 'DESC'], 10);

        return $this->render('staff/dashboard.html.twig', [
            'stats' => $stats,
            'my_tickets' => $myTickets,
            'recent_tickets' => $recentTickets,
        ]);
    }

    #[Route('/tickets', name: 'staff_tickets')]
    public function tickets(TicketRepository $ticketRepository, Request $request): Response
    {
        $status = $request->query->get('status');
        $category = $request->query->get('category');
        
        $criteria = [];
        if ($status) {
            $criteria['status'] = $status;
        }
        if ($category) {
            $criteria['category'] = $category;
        }

        $tickets = $ticketRepository->findBy($criteria, ['openedAt' => 'DESC']);
        
        return $this->render('staff/tickets/index.html.twig', [
            'tickets' => $tickets,
            'current_status' => $status,
            'current_category' => $category,
        ]);
    }

    #[Route('/tickets/{id}', name: 'staff_ticket_show')]
    public function showTicket(Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $statuses = $entityManager->getRepository(\App\Entity\Status::class)->findAll();
        
        return $this->render('staff/tickets/show.html.twig', [
            'ticket' => $ticket,
            'statuses' => $statuses,
        ]);
    }

    #[Route('/tickets/{id}/update-status', name: 'staff_ticket_update_status', methods: ['POST'])]
    public function updateTicketStatus(
        Ticket $ticket, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $newStatusId = $request->request->get('status_id');
        
        if ($newStatusId) {
            $status = $entityManager->getRepository(\App\Entity\Status::class)->find($newStatusId);
            if ($status) {
                $ticket->setStatus($status);
                
                // Si le statut devient "Fermé", définir la date de fermeture
                if ($status->getName() === 'Fermé' && !$ticket->getClosedAt()) {
                    $ticket->setClosedAt(new \DateTime());
                }
                
                $entityManager->flush();
                
                $this->addFlash('success', 'Statut du ticket mis à jour avec succès !');
            }
        }

        return $this->redirectToRoute('staff_ticket_show', ['id' => $ticket->getId()]);
    }

    #[Route('/my-tickets', name: 'staff_my_tickets')]
    public function myTickets(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findBy(['responsible' => $this->getUser()], ['openedAt' => 'DESC']);
        
        return $this->render('staff/tickets/my_tickets.html.twig', [
            'tickets' => $tickets,
        ]);
    }
}
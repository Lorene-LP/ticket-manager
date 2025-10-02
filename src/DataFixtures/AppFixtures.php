<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer les catégories selon les spécifications
        $categories = [
            ['name' => 'Incident', 'description' => 'Problème affectant le fonctionnement normal'],
            ['name' => 'Panne', 'description' => 'Arrêt complet d\'un service ou système'],
            ['name' => 'Évolution', 'description' => 'Demande d\'amélioration ou nouvelle fonctionnalité'],
            ['name' => 'Anomalie', 'description' => 'Comportement incorrect d\'un système'],
            ['name' => 'Information', 'description' => 'Demande d\'information ou de documentation'],
        ];

        $categoryObjects = [];
        foreach ($categories as $cat) {
            $category = new Category();
            $category->setName($cat['name']);
            $category->setDescription($cat['description']);
            $manager->persist($category);
            $categoryObjects[] = $category;
        }

        // Créer les statuts selon les spécifications
        $statuses = [
            ['name' => 'Nouveau', 'description' => 'Ticket nouvellement créé', 'color' => 'primary'],
            ['name' => 'Ouvert', 'description' => 'Ticket en cours de traitement', 'color' => 'warning'],
            ['name' => 'Résolu', 'description' => 'Ticket résolu en attente de validation', 'color' => 'info'],
            ['name' => 'Fermé', 'description' => 'Ticket fermé et archivé', 'color' => 'success'],
        ];

        $statusObjects = [];
        foreach ($statuses as $index => $stat) {
            $status = new Status();
            $status->setName($stat['name']);
            $status->setDescription($stat['description']);
            $status->setColor($stat['color']);
            // Le premier statut (Nouveau) est le statut par défaut
            $status->setIsDefault($index === 0);
            $manager->persist($status);
            $statusObjects[] = $status;
        }

        // Créer les utilisateurs
        // 1. Administrateur
        $admin = new User();
        $admin->setEmail('admin@agence.com');
        $admin->setFirstName('Jean');
        $admin->setLastName('Dupont');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // 2. Personnel de l'agence
        $staff1 = new User();
        $staff1->setEmail('marie.martin@agence.com');
        $staff1->setFirstName('Marie');
        $staff1->setLastName('Martin');
        $staff1->setRoles(['ROLE_STAFF']);
        $staff1->setPassword($this->passwordHasher->hashPassword($staff1, 'staff123'));
        $manager->persist($staff1);

        $staff2 = new User();
        $staff2->setEmail('pierre.durand@agence.com');
        $staff2->setFirstName('Pierre');
        $staff2->setLastName('Durand');
        $staff2->setRoles(['ROLE_STAFF']);
        $staff2->setPassword($this->passwordHasher->hashPassword($staff2, 'staff123'));
        $manager->persist($staff2);

        $manager->flush(); // Sauvegarder les utilisateurs, catégories et statuts d'abord

        $responsibleUsers = [$admin, $staff1, $staff2];

        // Créer des tickets de test
        $ticketData = [
            [
                'email' => 'client1@example.com',
                'description' => 'Le site web ne se charge plus depuis ce matin. J\'obtiens une erreur 500 quand j\'essaie d\'accéder à ma page de profil.',
                'category' => $categoryObjects[0], // Incident
                'status' => $statusObjects[0], // Nouveau
                'responsible' => null,
            ],
            [
                'email' => 'client2@example.com',
                'description' => 'Je souhaiterais avoir une nouvelle fonctionnalité pour exporter mes données en PDF. Cela m\'aiderait beaucoup pour mes rapports.',
                'category' => $categoryObjects[2], // Évolution
                'status' => $statusObjects[1], // Ouvert
                'responsible' => $staff1,
            ],
            [
                'email' => 'client3@example.com',
                'description' => 'Impossible de me connecter à mon compte depuis la mise à jour d\'hier. Le bouton de connexion ne fonctionne pas.',
                'category' => $categoryObjects[1], // Panne
                'status' => $statusObjects[2], // Résolu
                'responsible' => $staff2,
            ],
            [
                'email' => 'client4@example.com',
                'description' => 'Les couleurs de l\'interface ne s\'affichent pas correctement sur mon navigateur Chrome. Tout apparaît en noir et blanc.',
                'category' => $categoryObjects[3], // Anomalie
                'status' => $statusObjects[1], // Ouvert
                'responsible' => $admin,
            ],
            [
                'email' => 'client5@example.com',
                'description' => 'Pouvez-vous me donner des informations sur les tarifs de vos services premium ? Je n\'arrive pas à trouver cette information.',
                'category' => $categoryObjects[4], // Information
                'status' => $statusObjects[3], // Fermé
                'responsible' => $staff1,
            ],
            [
                'email' => 'client6@example.com',
                'description' => 'Mon tableau de bord affiche des données erronées. Les statistiques ne correspondent pas à la réalité de mes ventes.',
                'category' => $categoryObjects[3], // Anomalie
                'status' => $statusObjects[0], // Nouveau
                'responsible' => null,
            ],
        ];

        foreach ($ticketData as $index => $data) {
            $ticket = new Ticket();
            $ticket->setAuthorEmail($data['email']);
            $ticket->setDescription($data['description']);
            $ticket->setCategory($data['category']);
            $ticket->setStatus($data['status']);
            $ticket->setResponsible($data['responsible']);
            
            // Définir des dates d'ouverture variées
            $openedAt = new \DateTime();
            $openedAt->modify('-' . rand(1, 30) . ' days');
            $ticket->setOpenedAt($openedAt);
            
            // Si le ticket est fermé, définir une date de clôture
            if ($data['status']->getName() === 'Fermé') {
                $closedAt = clone $openedAt;
                $closedAt->modify('+' . rand(1, 10) . ' days');
                $ticket->setClosedAt($closedAt);
            }
            
            $manager->persist($ticket);
        }

        $manager->flush();
    }
}

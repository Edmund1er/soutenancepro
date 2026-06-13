<?php

namespace App\Controller\Admin;

use App\Entity\Enseignant;
use App\Entity\Soutenance;
use App\Form\EnseignantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/enseignant')]
#[IsGranted('ROLE_ADMIN')]
class EnseignantController extends AbstractController
{
    #[Route('/', name: 'app_enseignant_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 6;
        $search = $request->query->get('search', '');
        
        // Construction de la requete avec recherche
        $qb = $em->getRepository(Enseignant::class)
            ->createQueryBuilder('e');
        
        // Ajout du filtre de recherche si un terme est saisi
        if (!empty($search)) {
            $qb->where('e.nom LIKE :search')
               ->orWhere('e.prenom LIKE :search')
               ->orWhere('e.email LIKE :search')
               ->orWhere('e.specialite LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        // Compter le total pour la pagination
        $totalQuery = clone $qb;
        $total = count($totalQuery->getQuery()->getResult());
        $totalPages = ceil($total / $limit);
        
        // Appliquer la pagination
        $offset = ($page - 1) * $limit;
        $pagination = $qb->orderBy('e.id', 'DESC')
                         ->setFirstResult($offset)
                         ->setMaxResults($limit)
                         ->getQuery()
                         ->getResult();
        
        // Formulaire d'ajout
        $enseignant = new Enseignant();
        $form = $this->createForm(EnseignantType::class, $enseignant, [
            'action' => $this->generateUrl('app_enseignant_new'),
            'method' => 'POST'
        ]);
        
        // Formulaires de modification
        $editForms = [];
        foreach ($pagination as $e) {
            $editForms[$e->getId()] = $this->createForm(EnseignantType::class, $e, [
                'action' => $this->generateUrl('app_enseignant_edit', ['id' => $e->getId()]),
                'method' => 'POST'
            ])->createView();
        }
        
        return $this->render('admin/enseignant/index.html.twig', [
            'pagination' => $pagination,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);
    }
    
    #[Route('/new', name: 'app_enseignant_new', methods: ['POST'])]
   #[Route('/new', name: 'app_enseignant_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $enseignant = new Enseignant();
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            
            if (empty($plainPassword)) {
                $this->addFlash('danger', 'Le mot de passe est obligatoire pour creer un enseignant.');
                return $this->redirectToRoute('app_enseignant_index', ['page' => 1]);
            }
            
            // === AJOUT DU ROLE ENSEIGNANT ===
            $enseignant->setRoles(['ROLE_ENSEIGNANT']);
            
            $hashedPassword = $passwordHasher->hashPassword($enseignant, $plainPassword);
            $enseignant->setPassword($hashedPassword);
            
            $em->persist($enseignant);
            $em->flush();
            $this->addFlash('success', 'Enseignant ajoute avec succes');
        } else {
            $this->addFlash('danger', 'Erreur lors de l ajout');
        }
        
        return $this->redirectToRoute('app_enseignant_index', ['page' => 1]);
    }

    #[Route('/{id}/edit', name: 'app_enseignant_edit', methods: ['POST'])]
    public function edit(Request $request, Enseignant $enseignant, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(EnseignantType::class, $enseignant);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            
            // Le mot de passe est optionnel a la modification
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($enseignant, $plainPassword);
                $enseignant->setPassword($hashedPassword);
            }
            
            // S'assurer que le rôle est toujours present
            if (!in_array('ROLE_ENSEIGNANT', $enseignant->getRoles())) {
                $enseignant->setRoles(['ROLE_ENSEIGNANT']);
            }
            
            $em->flush();
            $this->addFlash('success', 'Enseignant modifie avec succes');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
            $this->addFlash('danger', 'Erreur lors de la modification');
        }
        
        $page = $request->request->get('page', 1);
        $search = $request->request->get('search', '');
        return $this->redirectToRoute('app_enseignant_index', ['page' => $page, 'search' => $search]);
    }
    
    #[Route('/{id}/delete', name: 'app_enseignant_delete', methods: ['POST'])]
    public function delete(Request $request, Enseignant $enseignant, EntityManagerInterface $em): Response
    {
        // Verification du token CSRF
        if (!$this->isCsrfTokenValid('delete' . $enseignant->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de securite invalide.');
            return $this->redirectToRoute('app_enseignant_index', ['page' => 1]);
        }
        
        // Verification : l'enseignant est-il membre d'un jury ?
        $soutenances = $em->getRepository(Soutenance::class)
            ->createQueryBuilder('s')
            ->where('s.president = :enseignant')
            ->orWhere('s.rapporteur = :enseignant')
            ->orWhere('s.examinateur = :enseignant')
            ->setParameter('enseignant', $enseignant)
            ->getQuery()
            ->getResult();
        
        if (count($soutenances) > 0) {
            $this->addFlash('danger', 'Cet enseignant ne peut pas etre supprime car il est membre d\'un jury de soutenance (president, rapporteur ou examinateur). Veuillez d\'abord modifier les soutenances concernees.');
            return $this->redirectToRoute('app_enseignant_index', ['page' => 1]);
        }
        
        try {
            $em->remove($enseignant);
            $em->flush();
            $this->addFlash('success', 'Enseignant supprime avec succes');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur technique lors de la suppression.');
        }
        
        return $this->redirectToRoute('app_enseignant_index', ['page' => 1]);
    }
}
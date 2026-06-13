<?php

namespace App\Controller\Admin;

use App\Entity\Etudiant;
use App\Entity\Soutenance;
use App\Form\EtudiantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/etudiant')]
#[IsGranted('ROLE_ADMIN')]
class EtudiantController extends AbstractController
{
    #[Route('/', name: 'app_etudiant_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 6;
        $search = $request->query->get('search', '');
        
        // Construction de la requete avec recherche
        $qb = $em->getRepository(Etudiant::class)
            ->createQueryBuilder('e');
        
        // Ajout du filtre de recherche si un terme est saisi
        if (!empty($search)) {
            $qb->where('e.nom LIKE :search')
               ->orWhere('e.prenom LIKE :search')
               ->orWhere('e.email LIKE :search')
               ->orWhere('e.filiere LIKE :search')
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
        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant, [
            'action' => $this->generateUrl('app_etudiant_new'),
            'method' => 'POST'
        ]);
        
        // Formulaires de modification
        $editForms = [];
        foreach ($pagination as $e) {
            $editForms[$e->getId()] = $this->createForm(EtudiantType::class, $e, [
                'action' => $this->generateUrl('app_etudiant_edit', ['id' => $e->getId()]),
                'method' => 'POST'
            ])->createView();
        }
        
        return $this->render('admin/etudiant/index.html.twig', [
            'pagination' => $pagination,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);
    }
    
    #[Route('/new', name: 'app_etudiant_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $etudiant = new Etudiant();
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($etudiant);
            $em->flush();
            $this->addFlash('success', 'Etudiant ajoute avec succes');
        } else {
            $this->addFlash('danger', 'Erreur lors de l ajout');
        }
        
        return $this->redirectToRoute('app_etudiant_index', ['page' => 1]);
    }
    
    #[Route('/{id}/edit', name: 'app_etudiant_edit', methods: ['POST'])]
    public function edit(Request $request, Etudiant $etudiant, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EtudiantType::class, $etudiant);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Etudiant modifie avec succes');
        } else {
            $this->addFlash('danger', 'Erreur lors de la modification');
        }
        
        $page = $request->request->get('page', 1);
        $search = $request->request->get('search', '');
        return $this->redirectToRoute('app_etudiant_index', ['page' => $page, 'search' => $search]);
    }
    
    #[Route('/{id}/delete', name: 'app_etudiant_delete', methods: ['POST'])]
    public function delete(Request $request, Etudiant $etudiant, EntityManagerInterface $em): Response
    {
        // Verification du token CSRF
        if (!$this->isCsrfTokenValid('delete' . $etudiant->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de securite invalide.');
            return $this->redirectToRoute('app_etudiant_index', ['page' => 1]);
        }
        
        // Verification : l'etudiant a-t-il une soutenance ?
        $soutenances = $em->getRepository(Soutenance::class)
            ->createQueryBuilder('s')
            ->where('s.etudiant = :etudiant')
            ->setParameter('etudiant', $etudiant)
            ->getQuery()
            ->getResult();
        
        if (count($soutenances) > 0) {
            $this->addFlash('danger', 'Cet etudiant ne peut pas etre supprime car il possede une soutenance enregistree. Veuillez d\'abord supprimer ou modifier sa soutenance.');
            return $this->redirectToRoute('app_etudiant_index', ['page' => 1]);
        }
        
        try {
            $em->remove($etudiant);
            $em->flush();
            $this->addFlash('success', 'Etudiant supprime avec succes');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur technique lors de la suppression.');
        }
        
        return $this->redirectToRoute('app_etudiant_index', ['page' => 1]);
    }
}
<?php

namespace App\Controller\Admin;

use App\Entity\Salle;
use App\Entity\Soutenance;
use App\Form\SalleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/salle')]
#[IsGranted('ROLE_ADMIN')]
class SalleController extends AbstractController
{
    #[Route('/', name: 'app_salle_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 6;
        
        $total = $em->getRepository(Salle::class)->count([]);
        $totalPages = ceil($total / $limit);
        
        $pagination = $em->getRepository(Salle::class)
            ->createQueryBuilder('s')
            ->orderBy('s.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        // Formulaire d'ajout
        $salle = new Salle();
        $form = $this->createForm(SalleType::class, $salle, [
            'action' => $this->generateUrl('app_salle_new'),
            'method' => 'POST'
        ]);
        
        // Formulaires de modification
        $editForms = [];
        foreach ($pagination as $s) {
            $editForms[$s->getId()] = $this->createForm(SalleType::class, $s, [
                'action' => $this->generateUrl('app_salle_edit', ['id' => $s->getId()]),
                'method' => 'POST'
            ])->createView();
        }
        
        return $this->render('admin/salle/index.html.twig', [
            'pagination' => $pagination,
            'page' => $page,
            'totalPages' => $totalPages,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);
    }
    
    #[Route('/new', name: 'app_salle_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $salle = new Salle();
        $form = $this->createForm(SalleType::class, $salle);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($salle);
            $em->flush();
            $this->addFlash('success', 'Salle ajoutee avec succes');
        } else {
            $this->addFlash('danger', 'Erreur lors de l ajout');
        }
        
        return $this->redirectToRoute('app_salle_index', ['page' => 1]);
    }
    
    #[Route('/{id}/edit', name: 'app_salle_edit', methods: ['POST'])]
    public function edit(Request $request, Salle $salle, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SalleType::class, $salle);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Salle modifiee avec succes');
        } else {
            $this->addFlash('danger', 'Erreur lors de la modification');
        }
        
        $page = $request->request->get('page', 1);
        return $this->redirectToRoute('app_salle_index', ['page' => $page]);
    }
    
    #[Route('/{id}/delete', name: 'app_salle_delete', methods: ['POST'])]
    public function delete(Request $request, Salle $salle, EntityManagerInterface $em): Response
    {
        // Verification du token CSRF
        if (!$this->isCsrfTokenValid('delete' . $salle->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de securite invalide.');
            return $this->redirectToRoute('app_salle_index', ['page' => 1]);
        }
        
        // Verification : des soutenances sont-elles programmees dans cette salle ?
        $soutenances = $em->getRepository(Soutenance::class)
            ->createQueryBuilder('s')
            ->where('s.salle = :salle')
            ->setParameter('salle', $salle)
            ->getQuery()
            ->getResult();
        
        if (count($soutenances) > 0) {
            $this->addFlash('danger', 'Cette salle ne peut pas etre supprimee car ' . count($soutenances) . ' soutenance(s) y sont programmees. Veuillez d\'abord supprimer ou modifier ces soutenances.');
            return $this->redirectToRoute('app_salle_index', ['page' => 1]);
        }
        
        try {
            $em->remove($salle);
            $em->flush();
            $this->addFlash('success', 'Salle supprimee avec succes');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur technique lors de la suppression.');
        }
        
        return $this->redirectToRoute('app_salle_index', ['page' => 1]);
    }
}
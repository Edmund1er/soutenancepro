<?php

namespace App\Controller\Admin;

use App\Entity\Enseignant;
use App\Entity\Soutenance;
use App\Form\SoutenanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/soutenance')]
#[IsGranted('ROLE_ADMIN')]
class SoutenanceController extends AbstractController
{
    #[Route('/', name: 'app_soutenance_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 6;
        $searchDate = $request->query->get('search_date', '');
        
        $qb = $entityManager->getRepository(Soutenance::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.etudiant', 'e')
            ->leftJoin('s.salle', 'sa')
            ->leftJoin('s.president', 'p')
            ->leftJoin('s.rapporteur', 'r')
            ->leftJoin('s.examinateur', 'ex')
            ->addSelect('e', 'sa', 'p', 'r', 'ex');
        
        if ($searchDate) {
            $date = \DateTime::createFromFormat('Y-m-d', $searchDate);
            if ($date) {
                $qb->where('s.date = :date')
                   ->setParameter('date', $date);
            }
        }
        
        $totalQuery = clone $qb;
        $total = count($totalQuery->getQuery()->getResult());
        $totalPages = ceil($total / $limit);
        
        $offset = ($page - 1) * $limit;
        $pagination = $qb->orderBy('s.date', 'ASC')
                         ->addOrderBy('s.heure', 'ASC')
                         ->setFirstResult($offset)
                         ->setMaxResults($limit)
                         ->getQuery()
                         ->getResult();
        
        $soutenance = new Soutenance();
        $form = $this->createForm(SoutenanceType::class, $soutenance, [
            'action' => $this->generateUrl('app_soutenance_new'),
            'method' => 'POST'
        ]);
        
        $editForms = [];
        foreach ($pagination as $s) {
            $editForms[$s->getId()] = $this->createForm(SoutenanceType::class, $s, [
                'action' => $this->generateUrl('app_soutenance_edit', ['id' => $s->getId()]),
                'method' => 'POST'
            ])->createView();
        }
        
        return $this->render('admin/soutenance/index.html.twig', [
            'pagination' => $pagination,
            'page' => $page,
            'totalPages' => $totalPages,
            'searchDate' => $searchDate,
            'form' => $form->createView(),
            'editForms' => $editForms,
        ]);
    }
    
    #[Route('/new', name: 'app_soutenance_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $soutenance = new Soutenance();
        $form = $this->createForm(SoutenanceType::class, $soutenance);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($soutenance);
            $entityManager->flush();
            $this->addFlash('success', 'Soutenance programmée avec succès');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }
        
        return $this->redirectToRoute('app_soutenance_index');
    }
    
    #[Route('/{id}/edit', name: 'app_soutenance_edit', methods: ['POST'])]
    public function edit(Request $request, Soutenance $soutenance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SoutenanceType::class, $soutenance);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Soutenance modifiée avec succès');
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }
        
        return $this->redirectToRoute('app_soutenance_index');
    }

    
    #[Route('/{id}/delete', name: 'app_soutenance_delete', methods: ['POST'])]
    public function delete(Request $request, Soutenance $soutenance, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $soutenance->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de securite invalide.');
            return $this->redirectToRoute('app_soutenance_index');
        }
        
        try {
            $etudiant = $soutenance->getEtudiant();
            $entityManager->remove($soutenance);
            $entityManager->flush();
            $this->addFlash('success', 'Soutenance annulee avec succes');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur technique lors de la suppression.');
        }
        
        return $this->redirectToRoute('app_soutenance_index');
    }
}
<?php

namespace App\Controller\Admin;

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
            ->addSelect('e', 'sa', 'p');
        
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
        
        // Formulaire d'ajout
        $soutenance = new Soutenance();
        $form = $this->createForm(SoutenanceType::class, $soutenance, [
            'action' => $this->generateUrl('app_soutenance_new'),
            'method' => 'POST'
        ]);
        
        // Formulaires de modification
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
    
    /**
     * Verifie les collisions avec intervalle d'1 heure
     */
    private function checkCollisions(Soutenance $soutenance, EntityManagerInterface $entityManager, ?int $excludeId = null): ?string
    {
        $date = $soutenance->getDate();
        $heure = $soutenance->getHeure();
        $salle = $soutenance->getSalle();
        $president = $soutenance->getPresident();
        $rapporteur = $soutenance->getRapporteur();
        $examinateur = $soutenance->getExaminateur();
        
        if (!$date || !$heure || !$salle || !$president) {
            return 'Tous les champs obligatoires doivent etre remplis';
        }
        
        $heureTimestamp = $heure->getTimestamp();
        $heureDebut = (new \DateTime())->setTimestamp($heureTimestamp - 3600);
        $heureFin = (new \DateTime())->setTimestamp($heureTimestamp + 3600);
        
        $repo = $entityManager->getRepository(Soutenance::class);
        
        $qb = $repo->createQueryBuilder('s')
            ->where('s.date = :date')
            ->andWhere('s.heure BETWEEN :heureDebut AND :heureFin')
            ->setParameter('date', $date)
            ->setParameter('heureDebut', $heureDebut)
            ->setParameter('heureFin', $heureFin);
        
        if ($excludeId) {
            $qb->andWhere('s.id != :id')
               ->setParameter('id', $excludeId);
        }
        
        // Collision salle
        $collisionSalle = (clone $qb)
            ->andWhere('s.salle = :salle')
            ->setParameter('salle', $salle)
            ->getQuery()
            ->getOneOrNullResult();
        
        if ($collisionSalle) {
            return 'Cette salle est deja occupee a cette date et heure';
        }
        
        // Collision president
        $collisionPresident = (clone $qb)
            ->andWhere('s.president = :president')
            ->setParameter('president', $president)
            ->getQuery()
            ->getOneOrNullResult();
        
        if ($collisionPresident) {
            return 'Cet enseignant (president) est deja dans un jury a cette date et heure';
        }
        
        // Collision rapporteur
        if ($rapporteur) {
            $collisionRapporteur = (clone $qb)
                ->andWhere('s.rapporteur = :rapporteur')
                ->setParameter('rapporteur', $rapporteur)
                ->getQuery()
                ->getOneOrNullResult();
            
            if ($collisionRapporteur) {
                return 'Cet enseignant (rapporteur) est deja dans un jury a cette date et heure';
            }
        }
        
        // Collision examinateur
        if ($examinateur) {
            $collisionExaminateur = (clone $qb)
                ->andWhere('s.examinateur = :examinateur')
                ->setParameter('examinateur', $examinateur)
                ->getQuery()
                ->getOneOrNullResult();
            
            if ($collisionExaminateur) {
                return 'Cet enseignant (examinateur) est deja dans un jury a cette date et heure';
            }
        }
        
        return null;
    }
    
    #[Route('/new', name: 'app_soutenance_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $soutenance = new Soutenance();
        $form = $this->createForm(SoutenanceType::class, $soutenance);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $error = $this->checkCollisions($soutenance, $entityManager);
            if ($error) {
                $this->addFlash('danger', $error);
                return $this->redirectToRoute('app_soutenance_index');
            }
            
            $entityManager->persist($soutenance);
            $entityManager->flush();
            $this->addFlash('success', 'Soutenance programmee avec succes');
        } else {
            $this->addFlash('danger', 'Erreur lors de la programmation');
        }
        
        return $this->redirectToRoute('app_soutenance_index');
    }
    
    #[Route('/{id}/edit', name: 'app_soutenance_edit', methods: ['POST'])]
    public function edit(Request $request, Soutenance $soutenance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SoutenanceType::class, $soutenance);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $error = $this->checkCollisions($soutenance, $entityManager, $soutenance->getId());
            if ($error) {
                $this->addFlash('danger', $error);
                return $this->redirectToRoute('app_soutenance_index');
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Soutenance modifiee avec succes');
        } else {
            $this->addFlash('danger', 'Erreur lors de la modification');
        }
        
        return $this->redirectToRoute('app_soutenance_index');
    }
    
    #[Route('/{id}/delete', name: 'app_soutenance_delete', methods: ['POST'])]
    public function delete(Request $request, Soutenance $soutenance, EntityManagerInterface $entityManager): Response
    {
        // Verification du token CSRF
        if (!$this->isCsrfTokenValid('delete' . $soutenance->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de securite invalide.');
            return $this->redirectToRoute('app_soutenance_index');
        }
        
        try {
            $entityManager->remove($soutenance);
            $entityManager->flush();
            $this->addFlash('success', 'Soutenance annulee avec succes');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur technique lors de la suppression.');
        }
        
        return $this->redirectToRoute('app_soutenance_index');
    }
}
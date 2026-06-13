<?php

namespace App\Controller\Enseignant;

use App\Entity\Soutenance;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/enseignant')]
#[IsGranted('ROLE_ENSEIGNANT')]
class EnseignantDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'enseignant_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Recuperer toutes les soutenances de l'enseignant
        $allSoutenances = $em->getRepository(Soutenance::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.etudiant', 'e')
            ->leftJoin('s.salle', 'sa')
            ->addSelect('e', 'sa')
            ->where('s.president = :user')
            ->orWhere('s.rapporteur = :user')
            ->orWhere('s.examinateur = :user')
            ->setParameter('user', $user)
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
        
        // Calcul des statistiques
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);
        $demain = clone $aujourdhui;
        $demain->modify('+1 day');
        
        $stats = [
            'total' => count($allSoutenances),
            'aVenir' => 0,
            'passees' => 0,
            'aujourdhui' => 0,
        ];
        
        foreach ($allSoutenances as $s) {
            $dateSoutenance = $s->getDate();
            if ($dateSoutenance >= $aujourdhui && $dateSoutenance < $demain) {
                $stats['aujourdhui']++;
            } elseif ($dateSoutenance >= $demain) {
                $stats['aVenir']++;
            } else {
                $stats['passees']++;
            }
        }
        
        // Recuperer uniquement les 4 dernieres pour le dashboard
        $recentesSoutenances = array_slice($allSoutenances, 0, 4);
        
        return $this->render('enseignant_ui/dashboard.html.twig', [
            'soutenances' => $recentesSoutenances,
            'stats' => $stats,
        ]);
    }
    
    #[Route('/mes-soutenances', name: 'enseignant_mes_soutenances')]
    public function mesSoutenances(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;
        
        // Requete pour compter le total
        $qb = $em->getRepository(Soutenance::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.etudiant', 'e')
            ->leftJoin('s.salle', 'sa')
            ->leftJoin('s.president', 'p')
            ->addSelect('e', 'sa', 'p')
            ->where('s.president = :user')
            ->orWhere('s.rapporteur = :user')
            ->orWhere('s.examinateur = :user')
            ->setParameter('user', $user);
        
        // Compter le total
        $totalQuery = clone $qb;
        $total = count($totalQuery->getQuery()->getResult());
        $totalPages = ceil($total / $limit);
        
        // Appliquer la pagination
        $offset = ($page - 1) * $limit;
        $soutenances = $qb->orderBy('s.date', 'DESC')
                          ->addOrderBy('s.heure', 'DESC')
                          ->setFirstResult($offset)
                          ->setMaxResults($limit)
                          ->getQuery()
                          ->getResult();
        
        return $this->render('enseignant_ui/mes_soutenances.html.twig', [
            'soutenances' => $soutenances,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }
    
    #[Route('/mes-jurys', name: 'enseignant_mes_jurys')]
    public function mesJurys(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 5;
        
        // Requete pour compter le total
        $qb = $em->getRepository(Soutenance::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.etudiant', 'e')
            ->leftJoin('s.salle', 'sa')
            ->leftJoin('s.president', 'p')
            ->leftJoin('s.rapporteur', 'r')
            ->leftJoin('s.examinateur', 'ex')
            ->addSelect('e', 'sa', 'p', 'r', 'ex')
            ->where('s.president = :user')
            ->orWhere('s.rapporteur = :user')
            ->orWhere('s.examinateur = :user')
            ->setParameter('user', $user);
        
        // Compter le total
        $totalQuery = clone $qb;
        $total = count($totalQuery->getQuery()->getResult());
        $totalPages = ceil($total / $limit);
        
        // Appliquer la pagination
        $offset = ($page - 1) * $limit;
        $soutenances = $qb->orderBy('s.date', 'DESC')
                          ->addOrderBy('s.heure', 'DESC')
                          ->setFirstResult($offset)
                          ->setMaxResults($limit)
                          ->getQuery()
                          ->getResult();
        
        return $this->render('enseignant_ui/mes_jurys.html.twig', [
            'soutenances' => $soutenances,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
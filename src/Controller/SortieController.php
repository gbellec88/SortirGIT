<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/sortie')]
final class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_list', methods: ['GET'])]
    public function list(SortieRepository $sortieRepository): Response
    {
        //aller chercher les cours en bdd , $courses est un tableau
        //$courses = $courseRepository->findAll();
        //avec des filtres
        //$courses = $courseRepository->findBy(["published" => true]);
        //$mminimumuration=2;
        $sorties = $sortieRepository->findAll();



        return $this->render('sortie/list.html.twig', [
            //on passe les cours à twig pour affichage
            'sorties' => $sorties,
        ]);
    }
}

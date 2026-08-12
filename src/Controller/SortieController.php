<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/sorties')]
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


    #[Route('/{id}', name: 'sortie_detail',requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id,
                           SortieRepository $sortieRepository,Request $request, EntityManagerInterface $em): Response

    {
        //allerchercher la sortie à partir de sin id
        $sortie = $sortieRepository->find($id);

        //aller chercher les participants de cette sortie
        $participants = $sortie->getParticipants();
        //aller chercher le lieu de cette sortie
        $lieu = $sortie->getLieu();
        //aller chercher l'organisateur de cette sortie
        $organisateur = $sortie->getOrganisateur();

        //si le souhait n'existe pas en BDD, on déclenche une erreur 404
        if (!$sortie) {
            throw $this->createNotFoundException("Ce souhait n'existe pas en BDD.");
        }



        return $this->render('sortie/detail.html.twig',[
            //on passe la sortie à  twig
            'sortie' => $sortie,
            'participants' => $participants,
            'lieu' => $lieu,
            'organisateur' => $organisateur,
        ]);
    }



}

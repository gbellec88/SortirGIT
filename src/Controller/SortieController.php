<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/sorties')]
final class SortieController extends AbstractController
{

    #[Route('/ajouter', name: 'sortie_create', methods: ['GET','POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $formSortie = $this->createForm(SortieType::class, $sortie);
        $formSortie->handleRequest($request);

        if ($formSortie->isSubmitted() && $formSortie->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        //créer un message qui va s'affciher une seule fois sur la prochaine page
        $this->addFlash("success","Sortie bien créé !");

        return $this->render('sortie/create.html.twig',[
            //on passe le formulaire à Twig
            "sortieForm" => $formSortie,
        ]);
    }


    #[Route('/', name: 'sorties_list', methods: ['GET'])]
    public function list(SortieRepository $sortieRepository,
                         CampusRepository $campusRepository,
                         Request $request): Response
    {


        //l'id du campus sélectionné ou au départ celui du user
        $campusId = $request->query->get('campus');
        if($campusId==null){
            $campusId = $this->getUser()->getCampus()->getId();
        }else{
            $campusId = $request->query->get('campus');
        }


        //déterminer si la case est cochée dont je suis l'organisateur
        $organisateur = $request->query->get('organisateur');

        if($organisateur==="0"){
            $organisateurId=$this->getUser()->getId();
        }else{
            $organisateurId = null;
        }

        //si la case est cochée , sorties auxquelles je suis inscrite
        $inscrit=$request->query->get('inscrit');
        if($inscrit==="0"){
            $inscritID=$this->getUser()->getId();
        }else{
            $inscritID = null;
        }


        //si la case est cochée ,sorties ou je ne suis pas inscrit
        $pasInscrit=$request->query->get('pasInscrit');
        if($pasInscrit==="0"){
            $pasInscritID=$this->getUser()->getId();
        }else{
            $pasInscritID = null;
        }

        //si la case terminèe est cochée
        $terminees=$request->query->get('terminees');
        if($terminees==="0"){
            $termineesBool=true;
        }else{
            $termineesBool=false;
        }

        //récupérer le filtrage entre une date de début et de fin
        $dateDebut = $request->query->get('dateDebut');
        $dateFin = $request->query->get('dateFin');
        //dd($dateDebut, $dateFin);

        if($dateDebut!=="" && $dateFin!==""){
            $Debut = new \DateTimeImmutable($request->query->get('dateDebut'));
            $Fin = new \DateTimeImmutable($request->query->get('dateFin'));

        }else{
            $Debut = null;
            $Fin = null;
        }



        //récupérer le filtrage sur le nom de la sortie contient
        $nomContient=$request->query->get('nomContient');




         if ($campusId) {
            //$sorties = $sortieRepository->findByCampusId($campusId);
            $sorties=$sortieRepository->findByFiltres($campusId,
                                                      $organisateurId,
                                                      $inscritID,
                                                      $pasInscritID,
                                                      $termineesBool,
                                                      $Debut,
                                                      $Fin,
                                                      $nomContient);
        } else {
            $sorties = $sortieRepository->findAll();
        }





        //récupérer tous les campus
        $campus= $campusRepository->findAll();


        return $this->render('sortie/list.html.twig', [
            //on passe les entités à twig pour affichage
            'sorties' => $sorties,
            'campus' => $campus,
            'selectedCampus' => $campusId,
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

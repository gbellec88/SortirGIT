<?php

namespace App\Controller;

use App\Close\Close;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ObjectManager;


#[Route('/sorties')]
final class SortieController extends AbstractController
{

    #[Route('/ajouter', name: 'sortie_create', methods: ['GET','POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager,EtatRepository $etatRepository): Response
    {
        //on récupère l'action à faire
        $action=$request->get('action');

        $campus=$this->getUser()->getCampus()->getNom();
        //récupérer l'organisateur
        $organisateur=new Participant();
        $organisateur=$this->getUser();

        //fixer état publier ou enregistrer pour modification
        $etat=new Etat();

        if($action=='publier'){
            $etat = $etatRepository->find(2);
        }

        if($action=='enregistrer'){
            $etat = $etatRepository->find(1);
        }

        if (!$organisateur) {
            throw $this->createNotFoundException('L\'organisateur est introuvable.');
        }

        if (!$etat) {
            throw $this->createNotFoundException('L\'état avec l\'ID 2 Ouverte est introuvable.');
        }



        $sortie = new Sortie();
        $formSortie = $this->createForm(SortieType::class, $sortie);
        $formSortie->handleRequest($request);

        if ($formSortie->isSubmitted() && $formSortie->isValid()) {
            $sortie->setOrganisateur($organisateur);
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();

            //créer un message qui va s'affciher une seule fois sur la prochaine page
            $this->addFlash("success","Sortie bien créé !");



            //redirection vers la page de détails de la sortie
            return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
        }
        return $this->render('sortie/create.html.twig',[
            //on passe le formulaire à Twig
            "sortieForm" => $formSortie,
            "campus"=>$campus,
        ]);

    }




    #[Route('/', name: 'sorties_list', methods: ['GET'])]
    public function list(SortieRepository $sortieRepository,
                         CampusRepository $campusRepository,
                         Request $request,
                         Close $close): Response

    {


        //l'id du campus sélectionné ou au départ celui du user
        $campusId = $request->query->get('campus');

        //dd($ca
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
        $dateDebut = $request->query->get('dateDebut') ?: null;
        $dateFin = $request->query->get('dateFin') ?: null;

        if ($dateDebut !== null && $dateFin !== null) {
            $Debut = new \DateTimeImmutable($dateDebut);
            $Fin = new \DateTimeImmutable($dateFin);
        } else {
            $Debut = null;
            $Fin = null;
        }



        //récupérer le filtrage sur le nom de la sortie contient
        $nomContient=$request->query->get('nomContient');


        $participant=$this->getUser();

        //dd($Debut,$Fin);

         if ($campusId) {
            //$sorties = $sortieRepository->findByCampusId($campusId);
            $sorties=$sortieRepository->findByFiltres($campusId,
                                                      $organisateurId,
                                                      $inscritID,
                                                      $pasInscritID,
                                                      $termineesBool,
                                                      $Debut,
                                                      $Fin,
                                                      $nomContient,
                                                      $participant);
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
            //on passe les entités à  twig
            'sortie' => $sortie,
            'participants' => $participants,
            'lieu' => $lieu,
            'organisateur' => $organisateur,
        ]);
    }


    //route pour modifier une sortie ou la supprimer
    #[Route('/{id}/modifier', name: 'sortie_modifier',requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    public function modifier(Sortie $sortie,Request $request,EntityManagerInterface $em,EtatRepository $etatRepository): Response
    {

        //on récupère l'action à faire
        $action=$request->get('action');

        //fixer état publier ou enregistrer pour modification
        $etat=new Etat();

        if($action=='publier'){
            $etat = $etatRepository->find(2);
        }

        if($action=='enregistrer'){
            $etat = $etatRepository->find(1);
        }

        //pour la SUPPRESSION //////////////////////////////////////////////
        if ($action === 'supprimer') {
            // Vérification du token
            if (!$this->isCsrfTokenValid(
                'delete-sortie-' . $sortie->getId(),
                $request->request->get('_token')
            )) {
                $this->addFlash('danger', 'Token CSRF invalide.');

                return $this->redirectToRoute('sorties_list');
            }

            $em->remove($sortie);
            $em->flush();

            $this->addFlash('success', 'La sortie a été supprimée.');

            return $this->redirectToRoute('sorties_list');
        }


       ///////////////////////////////////////////////////



        //créer le formulaire
        $sortieForm=$this->createForm(SortieType::class,$sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();

            //créer un message qui va s'affciher une seule fois sur la prochaine page
            $this->addFlash("success","Sortie bien modifiée !");

            //redirection vers la page de détails de la sortie
            return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
        }

        return $this->render('sortie/modifier.html.twig',[
            "sortie"=>$sortie,
            //on passe le formulaire à Twig
            "sortieForm" => $sortieForm,
        ]);



    }

    //route pour s'inscrire à une sortie
    #[Route('/{id}/inscrire', name: 'sortie_inscrire',requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    public function inscrire(Sortie $sortie,
                             EntityManagerInterface $em,
                             Close $close,
                             EtatRepository $etatRepository,
                             Request $request): Response
    {

        // Récupérer le participant connecté
        $participant = $this->getUser();


        if (!$participant) {
            throw $this->createNotFoundException("Ce participant n'existe pas.");

        }

        //ajout du participant connecté
         $sortie->addParticipant($participant);


         //passer la sortie à l'état clôturée si nb partcipants atteint
          $sortieAfermer=$close->SortieClose($sortie);
          if ($sortieAfermer) {
              $etat = $etatRepository->find(3);
              $sortie->setEtat($etat);
              $this->addFlash("warning", "La Sortie " . $sortie->getNom() . " est clôturée");
          }

        $em->persist($sortie);
        $em->flush();

        //créer un message qui va s'affciher une seule fois sur la prochaine page
        $this->addFlash("success","Vous êtes inscrit à la Sortie".$sortie->getNom());

        return $this->redirect($request->headers->get('referer'));

    }


    //route pour s'inscrire à une sortie
    #[Route('/{id}/desister', name: 'sortie_desister',requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    public function desister(Sortie $sortie,EntityManagerInterface $em,Request $request): Response
    {

        // Récupérer le participant connecté
        $participant = $this->getUser();

        if (!$participant) {
            throw $this->createNotFoundException("Ce participant n'existe pas.");

        }

        //on enlève le participant connecté de cette sortie
        $sortie->removeParticipant($participant);
        $em->persist($sortie);
        $em->flush();


        //créer un message qui va s'affciher une seule fois sur la prochaine page
        $this->addFlash("success","Vous êtes bien désinscrit de la Sortie".$sortie->getNom());

        return $this->redirect($request->headers->get('referer'));


    }


    //route pour annuler une sortie
   #[Route('/{id}/annuler', name: 'sortie_annuler',requirements: ['id' => '\d+'], methods: ['GET','POST'])]
   public function annuler(Sortie $sortie,
                           EntityManagerInterface $em,
                           EtatRepository $etatRepository,
                           Request $request): Response
   {
       $etat= new Etat();
       $etat = $etatRepository->find(6);


       $sortie->setEtat($etat);
       $em->persist($sortie);
       $em->flush();


       //créer un message qui va s'affciher une seule fois sur la prochaine page
       $this->addFlash("success","Vous avez annuler la Sortie".$sortie->getNom());

       return $this->redirect($request->headers->get('referer'));

   }

}



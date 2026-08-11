<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participants')]
final class ParticipantController extends AbstractController
{


    #[Route('/{id}/modifier', name: 'participant_edit',requirements: ['id' => '\d+'], methods: ['POST','GET'])]
    public function index(Participant $participant,Request $request,EntityManagerInterface $em): Response
    {
        //Créer le formulaire

        $participantForm=$this->createForm(ParticipantType::class, $participant);
        //traiter le formulaire
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            //on sauve en BDD
            $em->persist($participant);
            $em->flush();

            //redirection vers la page de détails du participant
            return $this->redirectToRoute('participant_edit', ['id' => $participant->getId()]);
        }


        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            //on passe le formulaire à twig
            'participantForm' => $participantForm,
        ]);
    }
}

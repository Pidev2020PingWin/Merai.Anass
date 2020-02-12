<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Evenement;
use UserBundle\Form\EventType;
use UserBundle\Entity\Demande;

class EvenementController extends Controller
{

    public function listmyAction(){
        $id=$this->getUser()->getId();
        $em=$this->getDoctrine()->getManager();
        $query = $em->createQuery(" SELECT e FROM UserBundle:Evenement e WHERE e.chefId=:id ");
        $query->setParameter('id',$id);
        $events = $query->getResult();
        $nb=count($events);
        return $this->render("@User/Evenement/listmy_evenement.html.twig",array(
            'evenments'=>$events,'nb'=>$nb
        ));
    }
    public function updateAction(Request $request){

        $id=$request->get('id');
        $em = $this->getDoctrine()->getManager();
        $evenment = $em->getRepository("UserBundle:Evenement")->find($id);
        $form = $this->createForm(EventType::class,$evenment);
        $form->handleRequest($request);
        $img=$evenment->getNomImage();
        if($form->isSubmitted()){
            $evenment->uploadProfilePicture();
            $evenment->setChefId($this->getUser());
            $em->persist($evenment);
            $em->flush();
            return $this->redirectToRoute("evenement_listmy");
        }
        return $this->render("@User/Evenement/update_evenement.html.twig",array(
            'form'=>$form->createView(),'evenment'=>$evenment ,'img'=>$img
        ));
    }
    public function deleteAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $evenment = $em->getRepository("UserBundle:Evenement")->find($id);
        $em->remove($evenment);
        $em->flush();
        return $this->redirectToRoute("evenement_listmy");

    }

    public function ajoutAction(Request $request){
        $asso = new Evenement();
        $form = $this->createForm(EventType::class,$asso);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $asso->setChefId($this->getUser());
           // $asso->setDescription($request->get('description'));
            $em = $this->getDoctrine()->getManager();
            $asso->uploadProfilePicture();
            $em->persist($asso);
            $em->flush();
            return $this->redirectToRoute("evenement_listmy");
        }
        return $this->render("@User/Evenement/add_evenement.html.twig",array(
            'form'=>$form->createView(),'asso'=>$asso
        ));

    }
    public function evenementAccessAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $msg="";
        $idu=$user->getId();
        $asso = $em->getRepository("UserBundle:Evenement")->find($id);
        //$demande = new Demande();
        $demande=$em->getRepository("UserBundle:Demande")->findOneBy(array('idu'=>$idu,'ide'=>$id));
        if(empty($demande)){
            $demande = new Demande();
            $demande->setEtat("en cours1");
            $demande->setIde($asso);
            $demande->setIdu($user);
            $em->persist($demande);
            $em->flush();
        } else if($demande->getEtat()=="en cours1"){$demande->setEtat("en cours2");}

        return $this->render("@User/Evenement/evenementJoinRequest.html.twig",array(
            'asso'=>$asso,'demande'=>$demande
        ));
    }



    public function listAction(Request $request){
        $user=$this->getUser();
     $idu=$user->getId();
        $em = $this->getDoctrine();
        $repository = $em->getRepository(Evenement::class);
        $asso = $repository->findAll();
        $paginator = $this->get('knp_paginator');
        $result = $paginator->paginate(
            $asso,
            $request->query->getInt('page',1),
            $request->query->getInt('limit',6)
        );
        $result->setTemplate('@User/Paginator/pagination.html.twig');
        $msg = array();
        //$rate = array();


        foreach($asso as $test ){
            $ide = $test->getIdEvent();
            $demande=$em->getRepository("UserBundle:Demande")->findOneBy(array('idu'=>$idu,'ide'=>$ide));
         if(empty($demande)){$msg[]="demande access";}
            else{$msg[]="verifier demande";}
        }


        return $this->render("@User/Evenement/list_evenement.html.twig",array( // nbadlou l test twig b list_evenement.html.twig
            'assos'=>$result ,'msgs'=>$msg
        ));
    }

    public function aboutEvenementAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $asso = $em->getRepository("UserBundle:Evenement")->find($id);
        return $this->render("@User/Evenement/aboutEvenement.html.twig" ,array(
            'asso'=>$asso
        ));

    }






}

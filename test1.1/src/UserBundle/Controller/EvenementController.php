<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\Evenement;
use UserBundle\Entity\Notification;
use UserBundle\Entity\Rating;
use UserBundle\Form\EventType;
use UserBundle\Entity\Demande;
use DateTime;
use UserBundle\Form\RatingType;


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
            $evenment->setNomEvent($request->get('nom_evenement'));

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
        $event = new Evenement();
        $form = $this->createForm(EventType::class,$event);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $event->setChefId($this->getUser());
            $event->setNomEvent($request->get('nom_evenement'));
            $sdt =$request->get('date');
            $dt = DateTime::createFromFormat("Y-m-d", $sdt);

           // $asso->setDescription($request->get('description'));
            $em = $this->getDoctrine()->getManager();
            $event->setDate($dt);
            $event->uploadProfilePicture();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute("evenement_listmy");
        }
        return $this->render("@User/Evenement/add_evenement.html.twig",array(
            'form'=>$form->createView(),'event'=>$event
        ));

    }
    public function evenementAccessAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $msg="";
        $idu=$user->getId();
        $event = $em->getRepository("UserBundle:Evenement")->find($id);
        //$demande = new Demande();
        $demande=$em->getRepository("UserBundle:Demande")->findOneBy(array('idu'=>$idu,'ide'=>$id));
        if(empty($demande)){
            $demande = new Demande();
            $demande->setEtat("en cours1");
            $demande->setIde($event);
            $demande->setIdu($user);
            $em->persist($demande);
            $em->flush();
            // methode rapide pour creer des notifications
            $notif = new Notification();
            $notif->setIdu($em->getRepository("UserBundle:User")->find(5));
            $notif
                ->setTitle('nouveau demande de participation !')
                ->setDescription($this->getUser()->getNom())
                ->setRoute('user_homepage')
                ->setParameters(array('ide'=> $id ,'idu'=> $idu));
            $em->persist($notif);
            $em->flush();

            $pusher = $this->get('mrad.pusher.notificaitons');
            $pusher->trigger($notif);
        } else if($demande->getEtat()=="en cours1"){$demande->setEtat("en cours2");}

        return $this->render("@User/Evenement/evenementJoinRequest.html.twig",array(
            'event'=>$event,'demande'=>$demande
        ));
    }



    public function listAction(Request $request){
        $user=$this->getUser();
        $idu=$user->getId();

        $em = $this->getDoctrine()->getManager();
        $asso = $em->getRepository("UserBundle:Evenement")->findAll();
        $paginator = $this->get('knp_paginator');

        $result = $paginator->paginate(
            $asso,
            $request->query->getInt('page',1),
            $request->query->getInt('limit',9)
        );
        $result->setTemplate('@User/Paginator/pagination.html.twig');
        $msg = array();
        $rate = array();


        foreach($asso as $test ){
            $ide = $test->getIdEvent();
            //$em->getRepository("UserBundle:Association")->findAcceptedAdherations($idu);
            $rateasso = $em->getRepository("UserBundle:Rating")->getEvenementsRatings($ide)[0]['val'];
            $demande=$em->getRepository("UserBundle:Demande")->findOneBy(array('idu'=>$idu,'ide'=>$ide));
            $rating=$em->getRepository("UserBundle:Rating")->findOneBy(array('idu'=>$idu,'ide'=>$ide));
            if(empty($demande)){$msg[]="demande access";}
            else{$msg[]="verifier demande";}
            if(empty($rating)){$rate[]=0;}
            else{$rate[]=$rateasso;}
        }


        return $this->render("@User/Evenement/list_evenement.html.twig",array( // nbadlou l test twig b list_association.html.twig
            'assos'=>$result ,'msgs'=>$msg,'rates'=>$rate
        ));
    }



    public function aboutEvenementAction(Request $request,$id){
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("UserBundle:Evenement")->find($id);
        $idu=$user->getId();
        $em = $this->getDoctrine()->getManager();
        $rate=$em->getRepository("UserBundle:Rating")->findOneBy(array('idu'=>$idu,'ide'=>$id));
        //$rate= new Rating();
        $form = $this->createForm(RatingType::class,$rate);
        $form->handleRequest($request);
        if($form->isSubmitted() && !empty($rate)){
            $rate->setIde($event);
            $rate->setIdu($user);
            $rate->setValue($request->get('star'));
            $em->persist($rate);
            $em->flush();
        }
        else if($form->isSubmitted()){
            $rate = new Rating();
            $form = $this->createForm(RatingType::class,$rate);
            $form->handleRequest($request);
            $rate->setIde($event);
            $rate->setIdu($user);
            $rate->setIde($event);
            $rate->setIdu($user);
            $rate->setValue($request->get('star'));
            $em->persist($rate);
            $em->flush();
        }
        return $this->render("@User/Evenement/aboutEvenement.html.twig" ,array(
            'event'=>$event,'form'=>$form->createView()
        ));

    }
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $requestString = $request->get('q');
        $posts =  $em->getRepository('UserBundle:Evenement')->findEntitiesByString($requestString);
        if(!$posts) {
            $result['posts']['error'] = "Event Not found :( ";
        } else {
            $result['posts'] = $this->getRealEntities($posts);
        }
        return new Response(json_encode($result));
    }

    public function getRealEntities($posts){
        foreach ($posts as $posts){
            $realEntities[$posts->getIdEvent()] = [$posts->getNomImage(),$posts->getNomEvent()];
        }
        return $realEntities;
    }








}

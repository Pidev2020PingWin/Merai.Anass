<?php
/**
 * Created by PhpStorm.
 * User: med
 * Date: 04/04/2019
 * Time: 00:37
 */

namespace AdminBundle\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Notification;


class EvenementController extends Controller
{
    public function listAction(){
        $em=$this->getDoctrine()->getManager();
        $demandes=$em->getRepository("UserBundle:Demande")->findBy([], ['etat' => 'ASC']);
        return $this->render("@Admin/Evenement/listDemandes.html.twig",array(
            'demande'=>$demandes
        ));
    }
    public function accepterAction(Request $request,$id){
        $em=$this->getDoctrine()->getManager();
        $demande=$em->getRepository("UserBundle:Demande")->find($id);
        $demande->setEtat("valider");
        $em->persist($demande);
        $em->flush();
        // methode rapide pour creer des notifications
        $notif = new Notification();
        $notif->setIdu($em->getRepository("UserBundle:User")->find($demande->getIdu()));
        $notif
            ->setTitle('demande de participation accepter')
            ->setDescription($this->getUser())
            ->setRoute('user_homepage')
            ->setParameters(array('ida'=> $id ));
        $em->persist($notif);
        $em->flush();

        $pusher = $this->get('mrad.pusher.notificaitons');
        $pusher->trigger($notif);
        return $this->redirectToRoute("demande_list");

    }

    public function refuserAction(Request $request,$id){
        $em=$this->getDoctrine()->getManager();
        $demande=$em->getRepository("UserBundle:Demande")->find($id);
        $demande->setEtat("refuser");
        $em->persist($demande);
        $em->flush();
        return $this->redirectToRoute("demande_list");

    }
    public function listRAction(){
        $em=$this->getDoctrine()->getManager();
        $demandes=$em->getRepository("UserBundle:Rating")->findBy([], ['ide' => 'ASC']);
        return $this->render("@Admin/Evenement/listRatings.html.twig",array(
            'demande'=>$demandes
        ));
    }

}
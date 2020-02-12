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



class EvenementController extends Controller
{
    public function listAction(){
        $em=$this->getDoctrine()->getManager();
        $demandes=$em->getRepository("UserBundle:Demande")->findAll();
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


}
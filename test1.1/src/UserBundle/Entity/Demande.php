<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use UserBundle\Repository\AssociationRepository;


/**
 * Demande
 *
 * @ORM\Table(name="demande", indexes={@ORM\Index(name="idU", columns={"idU", "idE"}), @ORM\Index(name="idE", columns={"idE"}), @ORM\Index(name="IDX_2694D7A5B1BBBA33", columns={"idU"})})
 * @ORM\Entity(repositoryClass="UserBundle\Repository\DemandeRepository")
 */
class Demande
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="etat",type="string",length=45,nullable=false)
     */
    private $etat;


    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idU", referencedColumnName="id")
     * })
     */
    private $idu;

    /**
     * @var \Evenement
     *
     * @ORM\ManyToOne(targetEntity="Evenement")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idE", referencedColumnName="id_event")
     * })
     */
    private $ide;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @param mixed $etat
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
    }

    /**
     * @return \Evenement
     */
    public function getIde()
    {
        return $this->ide;
    }

    /**
     * @param \Evenement $ide
     */
    public function setIde($ide)
    {
        $this->ide = $ide;
    }

    /**
     * @return \User
     */
    public function getIdu()
    {
        return $this->idu;
    }

    /**
     * @param \User $idu
     */
    public function setIdu($idu)
    {
        $this->idu = $idu;
    }

}

<?php

namespace App\Controller;

use App\Entity\Serie;

use App\Form\SerieType;
use App\Repository\SerieRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SerieController extends AbstractController
{
    /**
     * @Route("/series/{page}", name="serie_list", requirements={"page"= "\d+"})
     */
    public function list(int $page = 1, SerieRepository $serieRepository): Response
    {
        //TODO récupérer la liste de mes séries

        //récupérer le repository de série
        //$serieRepository = $this->getDoctrine()->getRepository(Serie::class);
        //$serieRepository = $entityManager->getRepository(Serie::class);

        //$series = $serieRepository->findAll();

        //$series = $serieRepository->findBy([], ["vote" => "DESC"], 50);



        $nbSeries = $serieRepository->count([]);
        $maxPage = ceil($nbSeries / 50);

        if($page >= 1 && $page <= $maxPage){
            $series = $serieRepository->findBestSeries($page);
        }else{
            throw $this->createNotFoundException("Oops ! 404 ! This page does not exist !");
        }


        return $this->render('serie/list.html.twig', [
            "series" => $series,
            "currentPage" => $page,
            "maxPage" => $maxPage,
        ]);
    }


    /**
     * @Route("/series/detail/{id}", name="serie_detail")
     */
    public function detail($id, SerieRepository $serieRepository): Response
    {
       //TODO récupérer la série en fonction de son id

        $serie = $serieRepository->find($id);
        if(!$serie){
            throw $this->createNotFoundException("Oops ! This serie does not exist ! ");

            //return $this->redirectToRoute('main_home');
        }

        return $this->render('serie/detail.html.twig', [
            "serie" => $serie
        ]);
    }



    /**
     * @Route("/series/create", name="serie_create")
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {

        //TODO générer un formulaire pour ajouter ma nouvelle série
        $serie = new Serie();
        $serieForm = $this->createForm(SerieType::class, $serie);
        $serie->setDateCreated(new \DateTime());

        $serieForm->handleRequest($request);

        if($serieForm->isSubmitted() && $serieForm->isValid()){
            $entityManager->persist($serie);
           $entityManager->flush();

           $this->addFlash('success', 'serie added !');
           return $this->redirectToRoute('serie_detail', ['id' => $serie->getId()]);
        }
        return $this->render('serie/create.html.twig', [
            'serieForm'=> $serieForm->createView()
        ]);
    }

    /**
     * @Route("/series/delete/{id}", name="serie_delete")
     */
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $serie = $entityManager->find(Serie::class, $id);
        $entityManager->remove($serie);
        $entityManager->flush();
        return $this->redirectToRoute('main_home');
    }

    /**
     * @Route("/series/edit/{id}", name="serie_create")
     */
    public function edit($id, Request $request, SerieRepository $serieRepository, EntityManagerInterface $entityManager): Response
    {


        $serie = $serieRepository->find($id);
        if(!$serie){
            throw $this->createNotFoundException('Opps this serie does not exist');
        }
        $serieForm = $this->createForm(SerieType::class, $serie);
         $serieForm->handleRequest($request);

        if($serieForm->isSubmitted() && $serieForm->isValid()){
            $serie->setDateModified(new \DateTime());
            $entityManager->persist($serie);
            $entityManager->flush();

            $this->addFlash('success', 'serie added !');
            return $this->redirectToRoute('serie_detail', ['id' => $serie->getId()]);
        }
        return $this->render('serie/edit.html.twig', [
            'serie'=> $serie,
            'serieForm'=> $serieForm->createView()
        ]);
    }
}

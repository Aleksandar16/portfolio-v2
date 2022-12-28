<?php

namespace App\Controller\admin;

use App\Entity\Technology;
use App\Form\TechnologyType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/technologies', name: 'technology_')]
class TechnologyController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/technology/index.html.twig', [
            'controller_name' => 'TechnologyController',
        ]);
    }

    #[Route('/ajouter', name: 'create')]
    public function createTechnology(Request $request, ManagerRegistry $doctrine): Response
    {
        $technology = new Technology();

        $form = $this->createForm(TechnologyType::class, $technology);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager = $doctrine->getManager();

            $technology = $form->getData();

            $entityManager->persist($technology);

            $entityManager->flush();

            return $this->redirectToRoute('technology_index');
        }

        return $this->render('admin/technology/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/modifier/{slug}', name: 'edit')]
    public function update(Request $request, ManagerRegistry $doctrine, Technology $technology, string $slug): Response
    {
        $entityManager = $doctrine->getManager();
        $technology = $entityManager->getRepository(Technology::class)->findOneBy(array('slug' => $slug));

        if (!$technology) {
            throw $this->createNotFoundException(
                'No product found for slug '.$slug
            );
        }

        $form = $this->createForm(TechnologyType::class, $technology);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager = $doctrine->getManager();

            $technology = $form->getData();

            $entityManager->persist($technology);

            $entityManager->flush();

            return $this->redirectToRoute('technology_index');
        }

        return $this->render('admin/technology/edit.html.twig', [
            'form' => $form,
        ]);
    }
}

<?php

namespace App\Controller\admin;

use App\Entity\Technology;
use App\Form\TechnologyType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/technologies', name: 'technology_')]
class TechnologyController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $technologies = $entityManager->getRepository(Technology::class)->findAll();

        return $this->render('admin/technology/index.html.twig', [
            'technologies' => $technologies,
        ]);
    }

    #[Route('/ajouter', name: 'create')]
    public function create(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $technology = new Technology();

        $form = $this->createForm(TechnologyType::class, $technology);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager = $doctrine->getManager();

            $technology = $form->getData();

            $logo = $form->get('logo')->getData();

            if ($logo) {
                $originalFilename = pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$logo->guessExtension();

                try {
                    $logo->move(
                        $this->getParameter('logo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $technology->setLogo($newFilename);
            }

            $entityManager->persist($technology);

            $entityManager->flush();

            return $this->redirectToRoute('technology_index');
        }

        return $this->render('admin/technology/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/modifier/{slug}', name: 'edit')]
    public function update(Request $request, ManagerRegistry $doctrine, Technology $technology, string $slug, SluggerInterface $slugger): Response
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

            $logo = $form->get('logo')->getData();

            if ($logo) {
                $originalFilename = pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$logo->guessExtension();

                try {
                    $logo->move(
                        $this->getParameter('logo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $technology->setLogo($newFilename);
            }

            $entityManager->persist($technology);

            $entityManager->flush();

            return $this->redirectToRoute('technology_index');
        }

        return $this->render('admin/technology/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'delete')]
    public function delete(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $technology = $entityManager->getRepository(Technology::class)->find($id);
        $entityManager->remove($technology);
        $entityManager->flush();
        return $this->redirectToRoute('technology_index');
    }
}

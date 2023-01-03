<?php

namespace App\Controller\admin;

use App\Entity\Project;
use App\Entity\Screen;
use App\Entity\Doc;
use App\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\Collections\ArrayCollection;

#[Route('/admin/projets', name: 'project_')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/project/index.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

    #[Route('/ajouter', name: 'create')]
    public function create(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $project = new Project();

        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $doctrine->getManager();

            $project = $form->getData();

            $screens = $form->get('screens')->getData();
            $docs = $form->get('docs')->getData();
            foreach($screens as $screen) {
                $originalFilename = pathinfo($screen->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$screen->guessExtension();

                try {
                    $screen->move(
                        $this->getParameter('screen_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $screen->setImage($newFilename);

                $entityManager->persist($screen);
            }

            foreach($docs as $doc) {
                $originalFilename = pathinfo($doc->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$doc->guessExtension();

                try {
                    $doc->move(
                        $this->getParameter('doc_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $doc->setImage($newFilename);
                
                $entityManager->persist($doc);
            }

            $entityManager->persist($project);

            $entityManager->flush();

            return $this->redirectToRoute('project_index');
        }

        return $this->renderForm('admin/project/new.html.twig', [
            'form' => $form,
        ]);
    }
}

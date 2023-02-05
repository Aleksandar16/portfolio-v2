<?php

namespace App\Controller\admin;

use App\Entity\Project;
use App\Entity\Screen;
use App\Entity\Doc;
use App\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/projets', name: 'project_')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $projects = $entityManager->getRepository(Project::class)->findAll();

        return $this->render('admin/project/index.html.twig', [
            'projects' => $projects,
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
            $images = $form->get('screens')->getData();

            foreach($images as $image){
                $fichier = md5(uniqid()).'.'.$image->guessExtension();

                $image->move(
                    $this->getParameter('screen_directory'),
                    $fichier
                );

                $img = new Screen();
                $img->setImage($fichier);
                $project->addScreen($img);
            }

            $docs = $form->get('docs')->getData();

            foreach($docs as $doc){
                $fichier = md5(uniqid()).'.'.$doc->guessExtension();

                $doc->move(
                    $this->getParameter('doc_directory'),
                    $fichier
                );

                $document = new Doc();
                $document->setDocument($fichier);
                $project->addDoc($document);
            }

            $i = 0;
            foreach ($project->getImages() as $image) {
                $uploadedImages = $form->get('images')[$i]->get('name')->getData();
                ++$i;
                $uploadedImage = $uploadedImages[0];
                $newFileName = $this->handleFile($slugger, $uploadedImage);
                $image->setName($newFileName);
            }

            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_index');
        }

        return $this->render('admin/project/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function handleFile($slugger, $image)
    {

        $extension = '.' . $image->guessExtension();
        $originalFileName = $slugger->slug($image->getClientOriginalName());
        $newFileName = $originalFileName . uniqid() . $extension;
        try {
            $image->move($this->getParameter('image_directory'), $newFileName);
        } catch (FileException $fe) {
            throw new \Exception("erreur");
        }

        return $newFileName;
    }

    #[Route('/modifier/{slug}', name: 'edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, string $slug): Response
    {
        $entityManager = $doctrine->getManager();
        $project = $entityManager->getRepository(Project::class)->findOneBy(array('slug' => $slug));
        $slug = $project->getSlug();
        $images = $project->getScreens();
        $documents = $project->getDocs();

        if (!$project) {
            throw $this->createNotFoundException(
                'No product found for slug '.$slug
            );
        }

        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('screens')->getData();

            foreach($images as $image){
                $fichier = md5(uniqid()).'.'.$image->guessExtension();

                $image->move(
                    $this->getParameter('screen_directory'),
                    $fichier
                );

                $img = new Screen();
                $img->setImage($fichier);
                $project->addScreen($img);
            }

            $docs = $form->get('docs')->getData();

            foreach($docs as $doc){
                $fichier = md5(uniqid()).'.'.$doc->guessExtension();

                $doc->move(
                    $this->getParameter('doc_directory'),
                    $fichier
                );

                $document = new Doc();
                $document->setDocument($fichier);
                $project->addDoc($document);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_edit', ['slug' => $slug]);
        }

        return $this->render('admin/project/new.html.twig', [
            'form' => $form,
            'screens' => $images,
            'docs' => $documents,
        ]);
    }

    #[Route('/supprimer/image/{id}', name: 'delete_image')]
    public function deleteImage(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $image = $entityManager->getRepository(Screen::class)->findOneBy(array('id' => $id));
        $project = $image->getProject();
        $slug = $project->getSlug();
        $entityManager->remove($image);
        $entityManager->flush();
        return $this->redirectToRoute('project_edit', ['slug' => $slug]);
    }

    #[Route('/supprimer/doc/{id}', name: 'delete_doc')]
    public function deleteDoc(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $doc = $entityManager->getRepository(Doc::class)->findOneBy(array('id' => $id));
        $project = $doc->getProject();
        $slug = $project->getSlug();
        $entityManager->remove($doc);
        $entityManager->flush();
        return $this->redirectToRoute('project_edit', ['slug' => $slug]);
    }
}

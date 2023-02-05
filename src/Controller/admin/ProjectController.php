<?php

namespace App\Controller\admin;

use App\Entity\Project;
use App\Entity\Doc;
use App\Form\ProjectType;
use Doctrine\Common\Collections\ArrayCollection;
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

            $j = 0;
            foreach ($project->getDocs() as $doc) {
                $uploadedDocs = $form->get('docs')[$j]->get('document')->getData();
                ++$j;
                $uploadedDoc = $uploadedDocs[0];
                $newFileName = $this->handleFileDoc($slugger, $uploadedDoc);
                $doc->setDocument($newFileName);
            }

            $i = 0;
            foreach ($project->getImages() as $image) {
                $uploadedImages = $form->get('images')[$i]->get('name')->getData();
                ++$i;
                $uploadedImage = $uploadedImages[0];
                $newFileName = $this->handleFileImage($slugger, $uploadedImage);
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

    public function handleFileImage($slugger, $image)
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

    public function handleFileDoc($slugger, $doc)
    {

        $extension = '.' . $doc->guessExtension();
        $originalFileName = $slugger->slug($doc->getClientOriginalName());
        $newFileName = $originalFileName . uniqid() . $extension;
        try {
            $doc->move($this->getParameter('doc_directory'), $newFileName);
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

        if (!$project) {
            throw $this->createNotFoundException(
                'No project found for slug '.$slug
            );
        }

        $originalImages = new ArrayCollection();
        $originalDocs = new ArrayCollection();

        foreach ($project->getImages() as $image) {
            $originalImages->add($image);
        }

        foreach ($project->getDocs() as $doc) {
            $originalDocs->add($doc);
        }

        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $doctrine->getManager();

            foreach ($originalImages as $image) {
                if (false === $project->getImages()->contains($image)) {
                    // remove the Task from the Tag
                    $tag->getTasks()->removeElement($task);

                    // if it was a many-to-one relationship, remove the relationship like this
                    // $tag->setTask(null);

                    $entityManager->persist($tag);

                    // if you wanted to delete the Tag entirely, you can also do that
                    // $entityManager->remove($tag);
                }
            }

            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_edit', ['slug' => $slug]);
        }

        return $this->render('admin/project/new.html.twig', [
            'form' => $form,
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

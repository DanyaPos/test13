<?php

namespace App\Controller;

use App\Entity\Developer;
use App\Entity\Project;
use App\Form\DeveloperType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeveloperController extends AbstractController
{
    #[Route('/developer/new', name: 'developer_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $developer = new Developer();


        $projects = $entityManager->getRepository(Project::class)->findAll();


        $form = $this->createForm(DeveloperType::class, $developer, [
            'projects' => $projects,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($developer);
            $entityManager->flush();

            return $this->redirectToRoute('developer_list');
        }

        return $this->render('developer/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/developers', name: 'developer_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $developers = $entityManager->getRepository(Developer::class)->findAll();

        return $this->render('developer/list.html.twig', [
            'developers' => $developers,
        ]);
    }

    #[Route('/developer/{id}/transfer', name: 'developer_transfer', methods: ['GET', 'POST'])]
    public function transfer(Developer $developer, Request $request, EntityManagerInterface $entityManager): Response
    {
        $projects = $entityManager->getRepository(Project::class)->findAll();

        if ($request->isMethod('POST')) {
            $newProject = $entityManager->getRepository(Project::class)->find($request->request->get('project'));

            if ($newProject && $newProject !== $developer->getProject()) {
                $developer->setProject($newProject);
                $entityManager->flush();

                return $this->redirectToRoute('developer_list');
            }
        }

        return $this->render('developer/transfer.html.twig', [
            'developer' => $developer,
            'projects' => $projects,
        ]);
    }

    #[Route('/developer/{id}/delete', name: 'developer_delete', methods: ['POST'])]
    public function delete(Developer $developer, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($developer);
        $entityManager->flush();

        return $this->redirectToRoute('developer_list');
    }
}

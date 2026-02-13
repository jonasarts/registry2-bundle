<?php

declare(strict_types=1);

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey as RegKey;
use jonasarts\Bundle\RegistryBundle\Form\Type\RegistryType;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

/**
 * Registry controller.
 */
#[Route('/registry')]
class RegistryController extends AbstractController
{
    public function __construct(
        private readonly RegistryInterface $registry,
    ) {
    }

    /**
     * Lists all Registry entities.
     */
    #[Route('/', name: 'registry_index')]
    public function indexAction(Request $request): Response
    {
        $entities = $this->all();

        return $this->render('@Registry/Registry/index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Displays a form to create a new Registry entity.
     */
    #[Route('/new', name: 'registry_new')]
    public function newAction(Request $request): Response
    {
        $entity = new RegKey();

        $form = $this->createForm(RegistryType::class, $entity, array('mode' => 'new'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // registryWrite will create a new registrykey
            $r = $this->write(
                $entity->getUserId(),
                $entity->getKey(),
                $entity->getName(),
                $entity->getType(),
                $entity->getValue()
            );

            if (!$r) {
                $this->addFlash('error', 'RegistryController.new: error on write');
            }

            return $this->redirectToRoute('registry_index');
        }

        return $this->render('@Registry/Registry/new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('registry_index'),
        ));
    }

    /**
     * Displays a form to edit a Registry entity.
     */
    #[Route('/edit', name: 'registry_edit')]
    public function editAction(Request $request): Response
    {
        $s = $request->query->get('entity');
        $entity = RegKey::deserialize($s);

        $form = $this->createForm(RegistryType::class, $entity, array('mode' => 'edit'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $request->isMethod('POST')) {
            // registryWrite can only update the value !!!
            $r = $this->write(
                $entity->getUserId(),
                $entity->getKey(),
                $entity->getName(),
                $entity->getType(),
                $entity->getValue()
            );

            if (!$r) {
                $this->addFlash('error', 'RegistryController.edit: error on write');
            }

            return $this->redirectToRoute('registry_index');
        }

        return $this->render('@Registry/Registry/edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('registry_index'),
        ));
    }

    /**
     * Delete a Registry entity.
     */
    #[Route('/delete', name: 'registry_delete')]
    public function deleteAction(Request $request): Response
    {
        $s = $request->query->get('entity');
        $entity = RegKey::deserialize($s);

        $r = $this->delete(
            $entity->getUserId(),
            $entity->getKey(),
            $entity->getName(),
            $entity->getType()
        );

        if (!$r) {
            $this->addFlash('error', 'RegistryController.delete: error on delete');
        }

        return $this->redirectToRoute('registry_index');
    }

    /**
     * Delete registry key from database.
     */
    private function delete(int $userid, string $key, string $name, string $type): bool
    {
        return $this->registry->registryDelete($userid, $key, $name, $type);
    }

    /**
     * Read registry key from database.
     */
    public function read(int $userid, string $key, string $name, string $type): mixed
    {
        return $this->registry->registryRead($userid, $key, $name, $type);
    }

    /**
     * Write registry key to database.
     */
    public function write(int $userid, string $key, string $name, string $type, mixed $value): bool
    {
        return $this->registry->registryWrite($userid, $key, $name, $type, $value);
    }

    /**
     * Return all registry keys from database.
     */
    public function all(): array
    {
        return $this->registry->registryAll();
    }
}

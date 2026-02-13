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
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey as SysKey;
use jonasarts\Bundle\RegistryBundle\Form\Type\SystemType;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;

/**
 * System controller.
 */
#[Route('/system')]
class SystemController extends AbstractController
{
    public function __construct(
        private readonly RegistryInterface $registry,
    ) {
    }

    /**
     * Lists all Registry entities.
     */
    #[Route('/', name: 'system_index')]
    public function indexAction(Request $request): Response
    {
        $entities = $this->all();

        return $this->render('@Registry/System/index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Displays a form to create a new System entity.
     */
    #[Route('/new', name: 'system_new')]
    public function newAction(Request $request): Response
    {
        $entity = new SysKey();

        $form = $this->createForm(SystemType::class, $entity, array('mode' => 'new'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // systemWrite will create a new systemkey
            $r = $this->write(
                $entity->getKey(),
                $entity->getName(),
                $entity->getType(),
                $entity->getValue()
            );

            if (!$r) {
                $this->addFlash('error', 'SystemController.new: error on write');
            }

            return $this->redirectToRoute('system_index');
        }

        return $this->render('@Registry/System/new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('system_index'),
        ));
    }

    /**
     * Displays a form to edit a System entity.
     */
    #[Route('/edit', name: 'system_edit')]
    public function editAction(Request $request): Response
    {
        $s = $request->query->get('entity');
        $entity = SysKey::deserialize($s);

        $form = $this->createForm(SystemType::class, $entity, array('mode' => 'edit'));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $request->isMethod('POST')) {
            // systemWrite can only update the value !!!
            $r = $this->write(
                $entity->getKey(),
                $entity->getName(),
                $entity->getType(),
                $entity->getValue()
            );

            if (!$r) {
                $this->addFlash('error', 'SystemController.edit: error on write');
            }

            return $this->redirectToRoute('system_index');
        }

        return $this->render('@Registry/System/edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('system_index'),
        ));
    }

    /**
     * Delete a System entity.
     */
    #[Route('/delete', name: 'system_delete')]
    public function deleteAction(Request $request): Response
    {
        $s = $request->query->get('entity');
        $entity = SysKey::deserialize($s);

        $r = $this->delete(
            $entity->getKey(),
            $entity->getName(),
            $entity->getType()
        );

        if (!$r) {
            $this->addFlash('error', 'SystemController.delete: error on delete');
        }

        return $this->redirectToRoute('system_index');
    }

    /**
     * Delete system key from database.
     */
    private function delete(string $key, string $name, string $type): bool
    {
        return $this->registry->systemDelete($key, $name, $type);
    }

    /**
     * Read system key from database.
     */
    public function read(string $key, string $name, string $type): mixed
    {
        return $this->registry->systemRead($key, $name, $type);
    }

    /**
     * Write system key to database.
     */
    public function write(string $key, string $name, string $type, mixed $value): bool
    {
        return $this->registry->systemWrite($key, $name, $type, $value);
    }

    /**
     * Return all registry keys from database.
     */
    public function all(): array
    {
        return $this->registry->systemAll();
    }
}

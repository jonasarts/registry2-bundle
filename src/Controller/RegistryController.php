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

use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey as RegKey;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use jonasarts\Bundle\RegistryBundle\Form\Type\RegistryType;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Registry controller.
 *
 * Access is restricted to the configured admin role; the whole controller is
 * only registered as a service when `registry.ui.enabled` is true.
 */
#[Route('/registry')]
class RegistryController extends AbstractController
{
    use EditableValue;

    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly string $baseTemplate,
        private readonly string $adminRole,
    ) {
    }

    /**
     * Lists all Registry entities.
     */
    #[Route('/', name: 'registry_index', methods: ['GET'])]
    public function indexAction(): Response
    {
        $this->denyAccessUnlessGranted($this->adminRole);

        return $this->render('@Registry/Registry/index.html.twig', [
            'entities' => $this->registry->registryAll(),
            'base_template' => $this->baseTemplate,
        ]);
    }

    /**
     * Displays a form to create a new Registry entity.
     */
    #[Route('/new', name: 'registry_new', methods: ['GET', 'POST'])]
    public function newAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->adminRole);

        $entity = new RegKey();

        $form = $this->createForm(RegistryType::class, $entity, ['mode' => 'new']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->write($entity->getUserId(), $entity->getKey(), $entity->getName(), $entity->getType(), $entity->getValue())) {
                $this->addFlash('error', 'RegistryController.new: error on write');
            }

            return $this->redirectToRoute('registry_index');
        }

        return $this->render('@Registry/Registry/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('registry_index'),
            'base_template' => $this->baseTemplate,
        ]);
    }

    /**
     * Displays a form to edit a Registry entity (value only).
     *
     * The key is identified by discrete, validated parameters rather than a
     * client-supplied serialized entity.
     */
    #[Route('/edit', name: 'registry_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->adminRole);

        $entity = new RegKey();

        if (!$request->isMethod('POST')) {
            // GET: prefill the form from validated discrete parameters
            $userId = $request->query->getInt('user_id');
            $key = (string) $request->query->get('key', '');
            $name = (string) $request->query->get('name', '');
            $type = RegistryKeyType::tryFrom((string) $request->query->get('type', ''));

            if ('' === $key || '' === $name || !$type instanceof RegistryKeyType) {
                throw $this->createNotFoundException('Invalid registry key reference');
            }

            $entity->setUserId($userId);
            $entity->setKey($key);
            $entity->setName($name);
            $entity->setType($type);
            $entity->setValue($this->valueToString($type, $this->registry->registryRead($userId, $key, $name, $type)));
        }

        $form = $this->createForm(RegistryType::class, $entity, ['mode' => 'edit']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->write($entity->getUserId(), $entity->getKey(), $entity->getName(), $entity->getType(), $entity->getValue())) {
                $this->addFlash('error', 'RegistryController.edit: error on write');
            }

            return $this->redirectToRoute('registry_index');
        }

        return $this->render('@Registry/Registry/edit.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('registry_index'),
            'base_template' => $this->baseTemplate,
        ]);
    }

    /**
     * Delete a Registry entity. POST + CSRF only.
     */
    #[Route('/delete', name: 'registry_delete', methods: ['POST'])]
    public function deleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->adminRole);

        if (!$this->isCsrfTokenValid('registry_delete', (string) $request->request->get('_token', ''))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $userId = $request->request->getInt('user_id');
        $key = (string) $request->request->get('key', '');
        $name = (string) $request->request->get('name', '');
        $type = RegistryKeyType::tryFrom((string) $request->request->get('type', ''));

        if ('' === $key || '' === $name || !$type instanceof RegistryKeyType) {
            throw $this->createNotFoundException('Invalid registry key reference');
        }

        if (!$this->delete($userId, $key, $name, $type)) {
            $this->addFlash('error', 'RegistryController.delete: error on delete');
        }

        return $this->redirectToRoute('registry_index');
    }

    private function delete(int $userid, string $key, string $name, RegistryKeyType $type): bool
    {
        return $this->registry->registryDelete($userid, $key, $name, $type);
    }

    private function write(int $userid, string $key, string $name, RegistryKeyType $type, mixed $value): bool
    {
        return $this->registry->registryWrite($userid, $key, $name, $type, $value);
    }
}

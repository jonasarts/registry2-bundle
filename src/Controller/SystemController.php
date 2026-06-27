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

use DateTimeInterface;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey as SysKey;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use jonasarts\Bundle\RegistryBundle\Form\Type\SystemType;
use jonasarts\Bundle\RegistryBundle\Registry\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * System controller.
 *
 * Access is restricted to the configured admin role; the whole controller is
 * only registered as a service when `registry.ui.enabled` is true.
 */
#[Route('/system')]
class SystemController extends AbstractController
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly string $baseTemplate,
        private readonly string $adminRole,
    ) {
    }

    /**
     * Lists all System entities.
     */
    #[Route('/', name: 'system_index', methods: ['GET'])]
    public function indexAction(): Response
    {
        $this->denyAccessUnlessGranted($this->adminRole);

        return $this->render('@Registry/System/index.html.twig', [
            'entities' => $this->registry->systemAll(),
            'base_template' => $this->baseTemplate,
        ]);
    }

    /**
     * Displays a form to create a new System entity.
     */
    #[Route('/new', name: 'system_new', methods: ['GET', 'POST'])]
    public function newAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->adminRole);

        $entity = new SysKey();

        $form = $this->createForm(SystemType::class, $entity, ['mode' => 'new']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->write($entity->getKey(), $entity->getName(), $entity->getType(), $entity->getValue())) {
                $this->addFlash('error', 'SystemController.new: error on write');
            }

            return $this->redirectToRoute('system_index');
        }

        return $this->render('@Registry/System/new.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('system_index'),
            'base_template' => $this->baseTemplate,
        ]);
    }

    /**
     * Displays a form to edit a System entity (value only).
     *
     * The key is identified by discrete, validated parameters rather than a
     * client-supplied serialized entity.
     */
    #[Route('/edit', name: 'system_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->adminRole);

        $entity = new SysKey();
        $form = $this->createForm(SystemType::class, $entity, ['mode' => 'edit']);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if (!$this->write($entity->getKey(), $entity->getName(), $entity->getType(), $entity->getValue())) {
                    $this->addFlash('error', 'SystemController.edit: error on write');
                }

                return $this->redirectToRoute('system_index');
            }
        } else {
            $key = (string) $request->query->get('key', '');
            $name = (string) $request->query->get('name', '');
            $type = RegistryKeyType::tryFrom((string) $request->query->get('type', ''));

            if ('' === $key || '' === $name || !$type instanceof RegistryKeyType) {
                throw $this->createNotFoundException('Invalid system key reference');
            }

            $entity->setKey($key);
            $entity->setName($name);
            $entity->setType($type);
            $entity->setValue($this->valueToString($this->registry->systemRead($key, $name, $type)));

            $form = $this->createForm(SystemType::class, $entity, ['mode' => 'edit']);
        }

        return $this->render('@Registry/System/edit.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('system_index'),
            'base_template' => $this->baseTemplate,
        ]);
    }

    /**
     * Delete a System entity. POST + CSRF only.
     */
    #[Route('/delete', name: 'system_delete', methods: ['POST'])]
    public function deleteAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted($this->adminRole);

        if (!$this->isCsrfTokenValid('system_delete', (string) $request->request->get('_token', ''))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $key = (string) $request->request->get('key', '');
        $name = (string) $request->request->get('name', '');
        $type = RegistryKeyType::tryFrom((string) $request->request->get('type', ''));

        if ('' === $key || '' === $name || !$type instanceof RegistryKeyType) {
            throw $this->createNotFoundException('Invalid system key reference');
        }

        if (!$this->delete($key, $name, $type)) {
            $this->addFlash('error', 'SystemController.delete: error on delete');
        }

        return $this->redirectToRoute('system_index');
    }

    private function delete(string $key, string $name, RegistryKeyType $type): bool
    {
        return $this->registry->systemDelete($key, $name, $type);
    }

    private function write(string $key, string $name, RegistryKeyType $type, mixed $value): bool
    {
        return $this->registry->systemWrite($key, $name, $type, $value);
    }

    /**
     * Render a stored value as an editable string for the form.
     */
    private function valueToString(mixed $value): string
    {
        return match (true) {
            null === $value => '',
            \is_string($value) => $value,
            \is_scalar($value) => (string) $value,
            $value instanceof DateTimeInterface => $value->format(DateTimeInterface::ATOM),
            default => json_encode($value, \JSON_THROW_ON_ERROR),
        };
    }
}

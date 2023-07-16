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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use jonasarts\Bundle\RegistryBundle\Entity\SystemKey as SysKey;
use jonasarts\Bundle\RegistryBundle\Form\SystemType;

/**
 * System controller.
 *
 * @Route("/system")
 */
class SystemController extends Controller
{
    /**
     * Lists all Registry entities.
     *
     * @Route("/", name="system_index")
     * @Template()
     */
    public function indexAction(Request $request): Response
    {
        $entities = $this->all();

        return $this->render('@Registry/System/index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Displays a form to create a new System entity.
     *
     * @Route("/new", name="system_new")
     */
    public function newAction(Request $request): Response
    {
        $entity = new SysKey();

        $form = $this->createForm(new SystemType(), $entity, array('mode' => 'new'));

        $form->handleRequest($request);

        if ($form->isSumitted() && $form->isValid()) {
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
     *
     * @Route("/edit", name="system_edit")
     */
    public function editAction(Request $request): Response
    {
        $s = $request->query->get('entity');
        $entity = SysKey::deserialize($s);

        $form = $this->createForm(new SystemType(), $entity, array('mode' => 'edit'));

        $form->handleRequest($request);

        //if ($form->isValid()) {
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
     *
     * @Route("/delete", name="system_delete")
     */
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
    private function delete($key, $name, $type)
    {
        $rm = $this->get('registry');

        return $rm->systemDelete($key, $name, $type);
    }

    /**
     * Read system key from database.
     */
    public function read($key, $name, $type)
    {
        $rm = $this->get('registry');

        return $rm->systemRead($key, $name, $type);
    }

    /**
     * Write system key to database.
     */
    public function write($key, $name, $type, $value)
    {
        $rm = $this->get('registry');

        return $rm->systemWrite($key, $name, $type, $value);
    }

    /**
     * Return all registry keys from database.
     */
    public function all()
    {
        $rm = $this->get('registry');

        return $rm->systemAll();
    }
}

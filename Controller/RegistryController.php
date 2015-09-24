<?php

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use jonasarts\Bundle\RegistryBundle\Model\RegistryKey as RegKey;
use jonasarts\Bundle\RegistryBundle\Form\Type\RegistryType;

/**
 * Registry controller.
 *
 * @Route("registry")
 */
class RegistryController extends Controller
{
    /**
     * Lists all Registry entities.
     *
     * @Route("/", name="registry_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $entities = $this->all();

        return array(
            'entities' => $entities,
            );
    }

    /** 
     * Displays a form to create a new Registry entity.
     *
     * @Route("/new", name="registry_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new RegKey();

        $form = $this->createForm(new RegistryType(), $entity, array('mode' => 'new'));
        $form->handleRequest($request);

        if ($form->isValid()) {
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

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('registry_index'),
            );
    }

    /**
     * Displays a form to edit a Registry entity.
     * 
     * @Route("/edit", name="registry_edit")
     * @Template()
     */
    public function editAction(Request $request)
    {
        $s = $request->query->get('entity');
        $entity = RegKey::deserialize($s);

        $form = $this->createForm(new RegistryType(), $entity, array('mode' => 'edit'));
        $form->handleRequest($request);

        //if ($form->isValid()) {
        if ($request->isMethod('POST')) {
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

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('registry_index'),
            );
    }

    /**
     * Delete a Registry entity.
     * 
     * @Route("/delete", name="registry_delete")
     */
    public function deleteAction(Request $request)
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
    private function delete($userid, $key, $name, $type)
    {
        $rm = $this->get('registry');

        return $rm->registryDelete($userid, $key, $name, $type);
    }

    /**
     * Read registry key from database.
     */
    public function read($userid, $key, $name, $type)
    {
        $rm = $this->get('registry');

        return $rm->readRegistry($userid, $key, $name, $type);
    }

    /**
     * Write registry key to database.
     */
    public function write($userid, $key, $name, $type, $value)
    {
        $rm = $this->get('registry');

        return $rm->registryWrite($userid, $key, $name, $type, $value);
    }

    /**
     * Return all registry keys from database.
     */
    public function all()
    {
        $rm = $this->get('registry');

        return $rm->registryAll();
    }
}

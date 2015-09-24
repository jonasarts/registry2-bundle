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
use jonasarts\Bundle\RegistryBundle\Model\SystemKey as SysKey;
use jonasarts\Bundle\RegistryBundle\Form\Type\SystemType;

/**
 * System controller.
 *
 * @Route("system")
 */
class SystemController extends Controller
{
    /**
     * Lists all Registry entities.
     *
     * @Route("/", name="system_index")
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
     * Displays a form to create a new System entity.
     *
     * @Route("/new", name="system_new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new SysKey();

        $form = $this->createForm(new SystemType(), $entity, array('mode' => 'new'));
        $form->handleRequest($request);

        if ($form->isValid()) {
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

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('system_index'),
            );
    }

    /**
     * Displays a form to edit a System entity.
     * 
     * @Route("/edit", name="system_edit")
     * @Template()
     */
    public function editAction(Request $request)
    {
        $s = $request->query->get('entity');
        $entity = SysKey::deserialize($s);

        $form = $this->createForm(new SystemType(), $entity, array('mode' => 'edit'));
        $form->handleRequest($request);

        //if ($form->isValid()) {
        if ($request->isMethod('POST')) {
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

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'back_url' => $this->generateUrl('system_index'),
            );
    }

    /**
     * Delete a System entity.
     * 
     * @Route("/delete", name="system_delete")
     */
    public function deleteAction(Request $request)
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

<?php
/*
 * Copyright REZO ZERO 2015
 *
 *
 * @file ExampleController.php
 * @copyright REZO ZERO 2015
 * @author Ambroise Maupate
 */

namespace Themes\RestApiTheme\Controllers;

use RZ\Roadiz\Core\Entities\Node;
use RZ\Roadiz\Core\Entities\Translation;
use Symfony\Component\HttpFoundation\Request;
use Themes\RestApiTheme\RestApiThemeApp;

/**
 * ExampleController class
 */
class ExampleController extends RestApiThemeApp
{
    /**
     * {@inheritdoc}
     */
    public function indexAction(
        Request $request,
        Node $node = null,
        Translation $translation = null
    ) {
        $this->prepareThemeAssignation($node, $translation);
        $this->getService('stopwatch')->start('twigRender');

        return $this->render('types/example.html.twig', $this->assignation);
    }
}

<?php
/*
 * Copyright REZO ZERO 2015
 *
 *
 * @file SitemapController.php
 * @copyright REZO ZERO 2015
 * @author Ambroise Maupate
 */
namespace Themes\RestApiTheme\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Themes\RestApiTheme\RestApiThemeApp;

/**
 * SitemapController.
 */
class SitemapController extends RestApiThemeApp
{
    public function sitemapAction(
        Request $request,
        $_locale = 'fr'
    ) {

        $this->prepareThemeAssignation(null, $this->bindLocaleFromRoute($request, $_locale));

        $this->assignation['home'] = $this->themeContainer['node.home']->getNodeSources()->first();

        /*
         * Add your own nodes grouped by their type.
         */
        /*
        $this->assignation['pages'] = $this->getService('nodeSourceApi')
            ->getBy(array(
            'node.nodeType' => $this->themeContainer['type.page'],
            'node.visible' => true,
            'translation' => $this->translation,
            ));

        */

        return new Response(
            $this->getTwig()->render('sitemap.xml.twig', $this->assignation),
            Response::HTTP_OK,
            array('content-type' => 'application/xml')
        );
    }
}

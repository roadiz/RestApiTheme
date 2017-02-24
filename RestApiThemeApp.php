<?php

namespace Themes\RestApiTheme;

use Pimple\Container;
use RZ\Roadiz\CMS\Controllers\FrontendController;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * RestApiThemeApp class
 */
class RestApiThemeApp extends FrontendController
{
    const VERSION = '0.7';

    protected static $themeName = 'RestApi theme';
    protected static $themeAuthor = 'Maxime Constantinian';
    protected static $themeCopyright = 'Ambroise Maupate, Julien Blanchet';
    protected static $themeDir = 'RestApiTheme';
    protected static $backendTheme = false;

    /**
     * Append objects to global container.
     *
     * @param \Pimple\Container $container
     */
    public static function setupDependencyInjection(Container $container)
    {
        parent::setupDependencyInjection($container);

        $container->extend('backoffice.entries', function (array $entries, $c) {
            /** @var UrlGenerator $urlGenerator */
            $urlGenerator = $c['urlGenerator'];
            /*
             * Add a test entry in your Backoffice
             */
            $entries['api'] = [
                'name' => 'Manage API',
                'path' => null,
                'icon' => 'uk-icon-file-text-o',
                'roles' => null,//array('ROLE_ACCESS_NEWS', 'ROLE_ACCESS_NEWS_DELETE'),
                'subentries' => [
                    'clientList' => [
                        'name' => 'api.list.client',
                        'path' => $urlGenerator->generate('clientAdminListPage'),
                        'icon' => 'uk-icon-file-text-o',
                        'roles' => null//array('ROLE_ACCESS_NEWS')
                    ],
                    'scopeList' => [
                        'name' => 'api.list.scope',
                        'path' => $urlGenerator->generate('scopeAdminListPage'),
                        'icon' => 'uk-icon-file-text-o',
                        'roles' => null//array('ROLE_ACCESS_NEWS')
                    ]
                ]
            ];

            return $entries;
        });
    }
}

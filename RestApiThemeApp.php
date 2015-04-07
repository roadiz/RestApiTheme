<?php

namespace Themes\RestApiTheme;

use RZ\Roadiz\CMS\Controllers\FrontendController;
use RZ\Roadiz\Core\Bags\SettingsBag;
use RZ\Roadiz\Core\Entities\Node;
use RZ\Roadiz\Core\Entities\Translation;
use Symfony\Component\HttpFoundation\Request;
use Pimple\Container;

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
    protected static $specificNodesControllers = array(
        // Put here your nodes which need a specific controller
        // instead of a node-type controller
    );

    /**
     * @param RZ\Roadiz\Core\Entities\Node        $node
     * @param RZ\Roadiz\Core\Entities\Translation $translation
     *
     * @return void
     */
    protected function prepareThemeAssignation(Node $node = null, Translation $translation = null)
    {
        parent::prepareThemeAssignation($node, $translation);

        $this->assignation['themeServices'] = $this->themeContainer;

        $this->assignation['head']['facebookUrl'] = SettingsBag::get('facebook_url');
        $this->assignation['head']['facebookClientId'] = SettingsBag::get('facebook_client_id');
        $this->assignation['head']['instagramUrl'] = SettingsBag::get('instagram_url');
        $this->assignation['head']['twitterUrl'] = SettingsBag::get('twitter_url');
        $this->assignation['head']['googleplusUrl'] = SettingsBag::get('googleplus_url');
        $this->assignation['head']['googleClientId'] = SettingsBag::get('google_client_id');
        $this->assignation['head']['maps_style'] = SettingsBag::get('maps_style');
        $this->assignation['head']['themeName'] = static::$themeName;
        $this->assignation['head']['themeVersion'] = static::VERSION;
    }

    /**
     * Append objects to global container.
     *
     * @param Pimple\Container $container
     */
    public static function setupDependencyInjection(Container $container)
    {
        parent::setupDependencyInjection($container);

        $container->extend('backoffice.entries', function (array $entries, $c) {

            /*
             * Add a test entry in your Backoffice
             */
            $entries['api'] = array(
                'name' => 'Manage API',
                'path' => null,
                'icon' => 'uk-icon-file-text-o',
                'roles' => null,//array('ROLE_ACCESS_NEWS', 'ROLE_ACCESS_NEWS_DELETE'),
                'subentries' => array(
                    'clientList' => array(
                        'name' => 'api.list.client',
                        'path' => $c['urlGenerator']->generate('clientAdminListPage'),
                        'icon' => 'uk-icon-file-text-o',
                        'roles' => null//array('ROLE_ACCESS_NEWS')
                    ),
                    'scopeList' => array(
                        'name' => 'api.list.scope',
                        'path' => $c['urlGenerator']->generate('scopeAdminListPage'),
                        'icon' => 'uk-icon-file-text-o',
                        'roles' => null//array('ROLE_ACCESS_NEWS')
                    )
                )
            );

            return $entries;
        });
    }
}

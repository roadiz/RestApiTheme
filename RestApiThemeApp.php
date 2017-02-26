<?php
/**
 * Copyright (c) 2017. Ambroise Maupate and Julien Blanchet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * Except as contained in this notice, the name of the ROADIZ shall not
 * be used in advertising or otherwise to promote the sale, use or other dealings
 * in this Software without prior written authorization from Ambroise Maupate and Julien Blanchet.
 *
 * @file RestApiThemeApp.php
 * @author Ambroise Maupate <ambroise@rezo-zero.com>
 */

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
                'name' => 'rest.api',
                'path' => null,
                'icon' => 'uk-icon-exchange',
                'roles' => null,
                'subentries' => [
                    'clientList' => [
                        'name' => 'api.list.client',
                        'path' => $urlGenerator->generate('clientAdminListPage'),
                        'icon' => 'uk-icon-users',
                        'roles' => null
                    ],
                    'scopeList' => [
                        'name' => 'api.list.scope',
                        'path' => $urlGenerator->generate('scopeAdminListPage'),
                        'icon' => 'uk-icon-eye',
                        'roles' => null
                    ],
                    'tokenList' => [
                        'name' => 'api.list.token',
                        'path' => $urlGenerator->generate('tokenAdminListPage'),
                        'icon' => 'uk-icon-ticket',
                        'roles' => null
                    ]
                ]
            ];

            return $entries;
        });
    }
}

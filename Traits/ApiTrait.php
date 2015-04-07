<?php
/**
 * Copyright © 2015, Ambroise Maupate and Julien Blanchet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
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
 * @file ApiTrait.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Themes\RestApiTheme\Storages;

trait ApiTrait
{
    protected $server;
    protected $authCodeGrant;
    protected $refrehTokenGrant;

    public function prepareApiServer()
    {
        // Set up the OAuth 2.0 authorization server
        $this->server = new \League\OAuth2\Server\AuthorizationServer();
        $this->server->setSessionStorage(new Storages\SessionStorage());
        $this->server->setAccessTokenStorage(new Storages\AccessTokenStorage());
        $this->server->setRefreshTokenStorage(new Storages\RefreshTokenStorage());
        $this->server->setClientStorage(new Storages\ClientStorage());
        $this->server->setScopeStorage(new Storages\ScopeStorage());
        $this->server->setAuthCodeStorage(new Storages\AuthCodeStorage());

        $this->authCodeGrant = new \League\OAuth2\Server\Grant\AuthCodeGrant();
        $this->server->addGrantType($this->authCodeGrant);
        $this->refreshTokenGrant = new \League\OAuth2\Server\Grant\RefreshTokenGrant();
        $this->server->addGrantType($this->refreshTokenGrant);
    }

    public function sendJson($statusCode, $data)
    {
        $response = new JsonResponse();
        $response->setData($data);
        $response->setStatusCode($statusCode);

        return $response;
    }
}

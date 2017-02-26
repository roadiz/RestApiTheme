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
 * @file TokenInfoController.php
 * @author Ambroise Maupate <ambroise@rezo-zero.com>
 */
namespace Themes\RestApiTheme\Controllers;

use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\HttpFoundation\JsonResponse;

class TokenInfoController extends ApiController
{
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function defaultAction()
    {
        $this->prepareResourceServer();
        // Check that access token is present
        try {
            $this->server->isValidRequest(false);
        } catch (OAuthException $e) {
            return $this->json([
                'error' => $e->errorType,
                'error_description' => $e->getMessage(),
                'error_uri' => ''
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $accessToken = $this->server->getAccessToken();
        $session = $this->server->getSessionStorage()->getByAccessToken($accessToken);

        return $this->json([
            'owner_id' => $session->getOwnerId(),
            'owner_type' => $session->getOwnerType(),
            'access_token' => [
                'id' => $accessToken->getId(),
                'expire_time' => $accessToken->getExpireTime(),
            ],
            'client_id' => $session->getClient()->getId(),
            'scopes' => $accessToken->getScopes(),
        ]);
    }
}

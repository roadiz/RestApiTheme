<?php
/**
 * Copyright © 2014, Ambroise Maupate and Julien Blanchet
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
 * @file RefreshTokenStorage.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Storages;

use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\RefreshTokenInterface;
use Themes\RestApiTheme\Entities\OAuth2RefreshToken;

use RZ\Roadiz\Core\Kernel;

class RefreshTokenStorage extends AbstractStorage implements RefreshTokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
        $result = Kernel::getService("em")->getRepository("Themes\RestApiTheme\Entities\OAuth2RefreshToken")
                                          ->findOneByRefreshToken($token);
        if ($result !== null) {
            $token = (new RefreshTokenEntity($this->server))
                        ->setId($result->getRefreshToken())
                        ->setAccessTokenId($result->getAccessToken()->getAccessToken())
                        ->setExpireTime($result->getExpireTime()->getTimestamp());
            return $token;
        }
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $accessToke)
    {
        $em = Kernel::getService("em");

        $accessToken = $em->getRepository()->findOneByAccessToken("Themes\RestApiTheme\Entities\OAuth2Session", $accessToken);

        $refreshToken = new OAuth2RefreshToken();
        $refreshToken->setRefreshToken($token);
        $datetime = new \DateTime();
        $refreshToken->setExpireTime($datetime->setTimestamp($expireTime));
        $refreshToken->setAccessToken($accessToken);

        $em->persist($refreshToken);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RefreshTokenEntity $token)
    {
        $em = Kernel::getService("em");

        $refreshToken = $em->getRepository("Themes\RestApiTheme\Entities\OAuth2RefreshToken")
                                               ->findOneByRefreshToken($token->getId());
        $em->remove($refreshToken);
        $em->flush();
    }
}

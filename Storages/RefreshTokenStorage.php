<?php
/**
 * Copyright © 2014, Ambroise Maupate and Julien Blanchet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the 'Software'), to deal
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
use League\OAuth2\Server\Storage\RefreshTokenInterface;
use Themes\RestApiTheme\Entities\OAuth2RefreshToken;

class RefreshTokenStorage extends AbstractStorage implements RefreshTokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
        /** @var OAuth2RefreshToken $result */
        $result = $this->em
            ->getRepository('Themes\RestApiTheme\Entities\OAuth2RefreshToken')
            ->findOneByValue($token);
        if ($result !== null) {
            $token = new RefreshTokenEntity($this->server);
            $token->setId($result->getValue())
                ->setExpireTime($result->getExpireTime()->getTimestamp());
            if ($result->getAccessToken() !== null) {
                $token->setAccessTokenId($result->getAccessToken()->getValue());
            }
            return $token;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $accessToken)
    {
        $accessTokenObj = $this->em
            ->getRepository('Themes\RestApiTheme\Entities\OAuth2AccessToken')
            ->findOneByValue($accessToken);

        if (null !== $accessTokenObj) {
            $refreshToken = new OAuth2RefreshToken();
            $refreshToken->setAccessToken($accessTokenObj);
            $refreshToken->setValue($token);
            $datetime = new \DateTime();
            $refreshToken->setExpireTime($datetime->setTimestamp($expireTime));

            $this->em->persist($refreshToken);
            $this->em->flush();
            $this->logger->info('New OAuth2RefreshToken id#' . $refreshToken->getId(), ['token' => $refreshToken]);
        } else {
            $this->logger->warning('No access_token (' . $accessToken . ') available for creating refresh_token');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RefreshTokenEntity $token)
    {
        $refreshToken = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2RefreshToken')
            ->findOneByValue($token->getId());
        if (null !== $refreshToken) {
            $this->logger->info('Delete refreshToken id#' . $refreshToken->getId(), ['token' => $refreshToken]);
            $this->em->remove($refreshToken);
            $this->em->flush();
        } else {
            $this->logger->warning('Cannot delete refreshToken.');
        }
    }
}

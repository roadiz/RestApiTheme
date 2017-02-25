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
 * @file AccessTokenStorage.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Storages;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use Themes\RestApiTheme\Entities\OAuth2AccessToken;

class AccessTokenStorage extends AbstractStorage implements AccessTokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
        $result = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2AccessToken')->findOneByValue($token);
        if ($result !== null) {
            $token = (new AccessTokenEntity($this->server))
                ->setId($result->getValue())
                ->setExpireTime($result->getExpireTime()->getTimestamp());
            return $token;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AccessTokenEntity $token)
    {
        $accessToken = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2AccessToken')
            ->findOneByValue($token->getId());

        $response = [];
        if ($accessToken->getScopes()->count() > 0) {
            foreach ($accessToken->getScopes() as $row) {
                $scope = (new ScopeEntity($this->server))->hydrate([
                    'id' => $row->getName(),
                    'description' => $row->getDescription(),
                ]);
                $response[] = $scope;
            }
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $sessionId)
    {
        $session = $this->em->find('Themes\RestApiTheme\Entities\OAuth2Session', $sessionId);

        if (null !== $session) {
            $accessToken = new OAuth2AccessToken();
            $accessToken->setValue($token);
            $datetime = new \DateTime();
            $accessToken->setExpireTime($datetime->setTimestamp($expireTime));
            $accessToken->setSession($session);

            $this->em->persist($accessToken);
            $this->em->flush();
            $this->logger->warning('New OAuth2AccessToken id#' . $accessToken->getId(), ['token' => $accessToken]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        $accessToken = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2AccessToken')
            ->findOneByValue($token->getId());
        $scope = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2Scope')
            ->findOneByName($scope->getId());
        $accessToken->addScope($scope);

        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AccessTokenEntity $token)
    {
        $accessToken = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2AccessToken')
            ->findOneByValue($token->getId());
        $this->logger->warning('Delete OAuth2AccessToken id#' . $accessToken->getId(), ['token' => $accessToken]);
        $this->em->remove($accessToken);
        $this->em->flush();
    }
}

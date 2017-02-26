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
 * @file ScopeStorage.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Storages;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\SessionInterface;
use RZ\Roadiz\Core\Entities\User;
use Themes\RestApiTheme\Entities\OAuth2AccessToken;
use Themes\RestApiTheme\Entities\OAuth2AuthCode;
use Themes\RestApiTheme\Entities\OAuth2Client;
use Themes\RestApiTheme\Entities\OAuth2ClientSession;
use Themes\RestApiTheme\Entities\OAuth2Scope;
use Themes\RestApiTheme\Entities\OAuth2Session;
use Themes\RestApiTheme\Entities\OAuth2UserSession;

class SessionStorage extends AbstractStorage implements SessionInterface
{
    /**
     * @param OAuth2Session $entity
     * @return SessionEntity
     */
    protected function getSessionFromEntity(OAuth2Session $entity)
    {
        $session = new SessionEntity($this->server);
        $session->setId($entity->getId());
        if (null !== $entity->getOwner()) {
            if ($entity->getOwner() instanceof OAuth2Client) {
                $type = 'client';
            } else {
                $type = 'user';
            }
            $session->setOwner($type, $entity->getOwner()->getId());
        }
        return $session;
    }
    /**
     * {@inheritdoc}
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        /** @var OAuth2AccessToken|null $result */
        $result = $this->em
            ->getRepository('Themes\RestApiTheme\Entities\OAuth2AccessToken')
            ->findOneByValue($accessToken->getId());

        if ($result !== null) {
            return $this->getSessionFromEntity($result->getSession());
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        /** @var OAuth2AuthCode|null $result */
        $result = $this->em
            ->getRepository('Themes\RestApiTheme\Entities\OAuth2AuthCode')
            ->findOneByValue($authCode->getId());
        if ($result !== null) {
            return $this->getSessionFromEntity($result->getSession());
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(SessionEntity $session)
    {
        $response = [];
        if (null !== $session->getId()) {
            $session = $this->em->find('Themes\RestApiTheme\Entities\OAuth2Session', $session->getId());
            if ($session->getScopes()->count() > 0) {
                /** @var OAuth2Scope $row */
                foreach ($session->getScopes() as $row) {
                    $scope = (new ScopeEntity($this->server))->hydrate([
                        'id' => $row->getName(),
                        'description' => $row->getDescription(),
                    ]);
                    $response[] = $scope;
                }
            }
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        /** @var OAuth2Client|null $client */
        $client = $this->em
            ->getRepository('Themes\RestApiTheme\Entities\OAuth2Client')
            ->findOneByClientId($clientId);

        if ($ownerType == 'client') {
            /** @var OAuth2Client|null $owner */
            $owner = $client;
            /** @var OAuth2ClientSession $session */
            $session = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2ClientSession')
                ->findOneBy([
                    'client' => $client
                ]);

            if ($session === null) {
                $session = new OAuth2ClientSession();
                $this->em->persist($session);
            }
            $session->setOwner($owner);
        } else {
            /** @var User|null $owner */
            $owner = $this->em->find('RZ\Roadiz\Core\Entities\User', $ownerId);
            /** @var OAuth2UserSession|null $session */
            $session = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2UserSession')
                ->findOneBy(['owner' => $owner, 'client' => $client]);

            if ($session === null) {
                $session = new OAuth2UserSession();
                $this->em->persist($session);
            }
            $session->setOwner($owner);
        }

        $session->setClient($client);
        $this->em->flush();

        return $session->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        /** @var OAuth2Session $session */
        $session = $this->em->find('Themes\RestApiTheme\Entities\OAuth2Session', $session->getId());
        /** @var OAuth2Scope $scope */
        $scope = $this->em
            ->getRepository('Themes\RestApiTheme\Entities\OAuth2Scope')
            ->findOneByName($scope->getId());

        $session->addScope($scope);
        $scope->addSession($session);
        $this->em->flush();
    }
}

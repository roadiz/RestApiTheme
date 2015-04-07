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
 * @file ScopeStorage.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Storages;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;

use Themes\RestApiTheme\Entities\OAuth2Scope;
use Themes\RestApiTheme\Entities\OAuth2AccessToken;
use Themes\RestApiTheme\Entities\OAuth2AuthCode;
use Themes\RestApiTheme\Entities\OAuth2Session;
use Themes\RestApiTheme\Entities\OAuth2ClientSession;
use Themes\RestApiTheme\Entities\OAuth2UserSession;

use RZ\Roadiz\Core\Kernel;

class SessionStorage extends AbstractStorage implements SessionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        $result = Kernel::getService("em")->getRepository("Themes\RestApiTheme\Entities\OAuth2AccessToken")
                                          ->findOneByValue($accessToken->getId());
        if ($result !== null) {
            $session = new SessionEntity($this->server);
            $session->setId($result->getSession()->getId());
            $type = (get_class($result->getSession()->getOwner()) == "Themes\RestApiTheme\Entities\OAuth2ClientSession") ? "client" : "user";
            $session->setOwner($type, $result->getSession()->getOwner()->getId());
            return $session;
        }
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        $result = Kernel::getService("em")->getRepository("Themes\RestApiTheme\Entities\OAuth2AuthCode")
                                          ->findOneByValue($authCode->getId());
        if ($result !== null) {
            $session = new SessionEntity($this->server);
            $session->setId($result->getSession()->getId());
            $type = (get_class($result->getSession()->getOwner()) == "Themes\RestApiTheme\Entities\OAuth2ClientSession") ? "client" : "user";
            $session->setOwner($type, $result->getSession()->getOwner()->getId());
            return $session;
        }
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(SessionEntity $session)
    {
        $session = Kernel::getService("em")->find("Themes\RestApiTheme\Entities\OAuth2Session", $session->getId());

        $response = [];
        if ($session->getScopes()->count() > 0) {
            foreach ($session->getScopes() as $row) {
                $scope = (new ScopeEntity($this->server))->hydrate([
                    'id'            =>  $row->getName(),
                    'description'   =>  $row->getDescription(),
                ]);
                $response[] = $scope;
            }
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        $em = Kernel::getService("em");

                $client = $em->getRepository("Themes\RestApiTheme\Entities\OAuth2Client")->findOneByClientId($clientId);

        if ($ownerType == "client") {
            $owner = $em->find("Themes\RestApiTheme\Entities\OAuth2Client", $ownerId);
            $session = $em->getRepository("Themes\RestApiTheme\Entities\OAuth2Session")
                          ->findOneBy(['owner' => $owner, 'client' => $client]);

            if ($session === null) {
                $session = new OAuth2ClientSession();
                $em->persist($session);
            }


        } else {
            $owner = $em->find("RZ\Roadiz\Core\Entities\User", $ownerId);
            $session = $em->getRepository("Themes\RestApiTheme\Entities\OAuth2UserSession")
                          ->findOneBy(['owner' => $owner, 'client' => $client]);

            if ($session === null) {
                $session = new OAuth2UserSession();
                $em->persist($session);
            }

        }

        $session->setOwner($owner);
        $session->setClient($client);

        $em->flush();

        return $session->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        $em = Kernel::getService("em");
        $session = Kernel::getService("em")->find("Themes\RestApiTheme\Entities\OAuth2Session", $session->getId());
        $scope = Kernel::getService("em")->getRepository("Themes\RestApiTheme\Entities\OAuth2Scope")->findOneByName($scope->getId());

        $session->addScope($scope);

        $em->flush();
    }
}

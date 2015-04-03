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
 * @file AuthCodeStorage.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Storages;

use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AuthCodeInterface;
use Themes\RestApiTheme\Entities\OAuth2AuthCode;

use RZ\Roadiz\Core\Kernel;

class AuthCodeStorage extends AbstractStorage implements AuthCodeInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
        $q = Kernel::getService("em")->createQuery("SELECT a FROM Themes\RestApiTheme\Entities\OAuth2AuthCode a WHERE a.expireTime >= ?1 AND a.authCode = ?2");
        $q->setParameter(1, new \DateTime());
        $q->setParameter(2, $code);
        $result = $q->getResult()[0];

        if ($result !== null) {
            $token = (new AuthCodeEntity($this->server))
                        ->setId($result->getAuthCode())
                        ->setRedirectUri($result->getSession()->getClient()->getRedirectUri())
                        ->setExpireTime($result->getExpireTime()->getTimestamp());
            return $token;
        }
    }

    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        $em = Kernel::getService("em");

        $session = $em->find("Themes\RestApiTheme\Entities\OAuth2Session", $sessionId);

        $authCode = new OAuth2AuthCode();
        $authCode->setAuthCode($token);
        $authCode->setSession($session);
        $datetime = new \DateTime();
        $authCode->setExpireTime($datetime->setTimestamp($expireTime));

        $em->persist($authCode);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AuthCodeEntity $token)
    {
        $authCode = Kernel::getService("em")->getRepository("Themes\RestApiTheme\Entities\OAuth2AuthCode")
                                               ->findOneByAuthCode($token->getId());

        $response = [];
        if ($authCode->getScopes()->count() > 0) {
            foreach ($authCode->getScopes() as $row) {
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
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        $em = Kernel::getService("em");

        $authCode = $em->getRepository("Themes\RestApiTheme\Entities\OAuth2AuthCode")
                       ->findOneByAuthCode($token->getId());
        $scope = $em->getRepository("Themes\RestApiTheme\Entities\OAuth2Scope")
                    ->findOneByName($scope->getId());
        $authCode->addScope($scope);

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AuthCodeEntity $token)
    {
        $em = Kernel::getService("em");

        $authCode = $em->getRepository("Themes\RestApiTheme\Entities\OAuth2AuthCode")
                          ->findOneByAuthCode($token->getId());
        $em->remove($authCode);
        $em->flush();
    }
}

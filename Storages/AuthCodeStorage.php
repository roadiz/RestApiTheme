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
 * @file AuthCodeStorage.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Storages;

use Doctrine\ORM\ORMException;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AuthCodeInterface;
use Themes\RestApiTheme\Entities\OAuth2AuthCode;
use Themes\RestApiTheme\Entities\OAuth2Session;

class AuthCodeStorage extends AbstractStorage implements AuthCodeInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
        try {
            $q = $this->em->createQuery("SELECT a FROM Themes\RestApiTheme\Entities\OAuth2AuthCode a WHERE a.expireTime >= :expireTime AND a.value = :value");
            $q->setParameter('expireTime', new \DateTime());
            $q->setParameter('value', $code);
            $result = $q->getSingleResult();

            if ($result !== null) {
                $token = (new AuthCodeEntity($this->server))
                    ->setId($result->getValue())
                    ->setRedirectUri($result->getSession()->getClient()->getRedirectUri())
                    ->setExpireTime($result->getExpireTime()->getTimestamp());
                return $token;
            }
        } catch (ORMException $e) {
            // do nothing
            return null;
        }
        return null;
    }

    /**
     * @param string $token
     * @param int $expireTime
     * @param int $sessionId
     * @param string $redirectUri
     */
    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        /** @var OAuth2Session $session */
        $session = $this->em->find('Themes\RestApiTheme\Entities\OAuth2Session', $sessionId);
        /** @var OAuth2AuthCode $authCode */
        $authCode = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2AuthCode')->findOneBySession($session);

        if ($authCode === null) {
            $authCode = new OAuth2AuthCode();
            $authCode->setSession($session);
            $this->em->persist($authCode);
            $this->em->flush();
        }

        $authCode->setValue($token);
        $datetime = new \DateTime();
        $authCode->setExpireTime($datetime->setTimestamp($expireTime));

        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AuthCodeEntity $token)
    {
        $authCode = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2AuthCode')
                         ->findOneByValue($token->getId());

        $response = [];
        if ($authCode->getScopes()->count() > 0) {
            foreach ($authCode->getScopes() as $row) {
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
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        $authCode = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2AuthCode')
                         ->findOneByValue($token->getId());
        $scope = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2Scope')
                      ->findOneByName($scope->getId());
        $authCode->addScope($scope);

        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AuthCodeEntity $token)
    {
        $authCode = $this->em->getRepository('Themes\RestApiTheme\Entities\OAuth2AuthCode')
                         ->findOneByValue($token->getId());
        $this->em->remove($authCode);
        $this->em->flush();
    }
}

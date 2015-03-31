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
 * @file OAuth2AuthCode.php
 * @author Maxime Constantinian
 */
 namespace Themes\RestApiTheme\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use Themes\RestApiTheme\Entities\OAuth2Session;
use Themes\RestApiTheme\Entities\OAuth2Scope;

/**
 * OAuth2AuthCode store all information about auth code.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\Core\Repositories\EntityRepository")
 * @ORM\Table(name="oauth_auth_code")
 */
class OAuth2AuthCode extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    private $authCode;

    /**
     * @return string
     */
    public function getAuthCode() {
        return $this->authCode;
    }

    /**
     * @param string $authCode
     *
     * @return $this
     */
    public function setAuthCode($authCode) {
        $this->authCode = $authCode;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="OAuth2Session")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id", onDelete="CASCADE")
     **/
     private $sessionId;

     /**
      * @return Themes\RestApiTheme\Entities\OAuth2Session
      */
     public function getSessionId() {
         return $this->sessionId;
     }

     /**
      * @param Themes\RestApiTheme\Entities\OAuth2Session $ownerId
      *
      * @return $this
      */
      public function setSessionId($sessionId) {
          $this->sessionId = $sessionId;
          return $this;
      }

      /**
       * @ORM\Column(type="datetime", nullable=false)
       * @var DateTime
       */
      private $expireTime;

      /**
       * @return DateTime
       */
      public function getExpireTime() {
          return $this->expireTime;
      }

      /**
       * @param DateTime $expireTime
       *
       * @return $this
       */
      public function setExpireTime($expireTime) {
          $this->expireTime = $expireTime;
          return $this;
      }

      /**
       * @ORM\ManyToMany(targetEntity="OAuth2Scope", inversedBy="authCodes", cascade={"remove"})
       * @ORM\JoinTable(name="oauth_auth_code_scope")
       * @var ArrayCollection
       **/
       private $scopes;

       /**
        * @return ArrayCollection
        */
       public function getScopes() {
           return $this->scopes;
       }

       /**
        * @param OAuth2Scope $scope
        *
        * @return $this
        */
       public function addScope($scope) {
           $this->scopes->add($scope);
           return $this;
       }

       public function __construct() {
           $this->scopes = new ArrayCollection;
       }
}

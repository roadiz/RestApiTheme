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
 * @file OAuth2Scope.php
 * @author Maxime Constantinian
 */
 namespace Themes\RestApiTheme\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use Themes\RestApiTheme\Entities\OAuth2Session;
use Themes\RestApiTheme\Entities\OAuth2AccessToken;
use Themes\RestApiTheme\Entities\OAuth2AuthCode;

/**
 * OAuth2Scope store all information about OAuth2 scope.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\Core\Repositories\EntityRepository")
 * @ORM\Table(name="oauth_scope")
 */
class OAuth2Scope extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
     private $name;

     /**
      * @return string
      */
     public function getName() {
         return $this->name;
     }

     /**
      * @param string $name
      *
      * @return $this
      */
      public function setName() {
          $this->name = $name;
          return $this;
      }

      /**
       * @ORM\Column(type="string", nullable=true)
       * @var string
       */
       private $description;

       /**
        * @return string
        */
       public function getDescription() {
           return $this->description;
       }

       /**
        * @param string $name
        *
        * @return $this
        */
        public function setDescription($description) {
            $this->description = $description;
            return $this;
        }

        /**
         * @ORM\ManyToMany(targetEntity="OAuth2AccessToken", mappedBy="scopes")
         * @var ArrayCollection
         **/
         private $accessTokens;

         /**
          * @return ArrayCollection
          */
         public function getAccessTokens() {
             return $this->accessTokens;
         }

         /**
          * @param OAuth2AccessToken $scope
          *
          * @return $this
          */
         public function addAccessToken($accessToken) {
             $this->accessTokens->add($accessToken);
             return $this;
         }

         /**
          * @ORM\ManyToMany(targetEntity="OAuth2AuthCode", mappedBy="scopes")
          * @var ArrayCollection
          **/
          private $authCodes;

          /**
           * @return ArrayCollection
           */
          public function getAuthCodes() {
              return $this->authCodes;
          }

          /**
           * @param OAuth2AuthCode $scope
           *
           * @return $this
           */
          public function addAuthCode($authCode) {
              $this->authCodes->add($authCode);
              return $this;
          }

          /**
           * @ORM\ManyToMany(targetEntity="OAuth2Session", mappedBy="scopes")
           * @var ArrayCollection
           **/
           private $sessions;

           /**
            * @return ArrayCollection
            */
           public function getSessions() {
               return $this->sessions;
           }

           /**
            * @param OAuth2Session $scope
            *
            * @return $this
            */
           public function addSession($session) {
               $this->sessions->add($session);
               return $this;
           }

         public function __construct() {
             $this->accessTokens = new ArrayCollection;
             $this->authCodes = new ArrayCollection;
             $this->sessions = new ArrayCollection;
         }
}

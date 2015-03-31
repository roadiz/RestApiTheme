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
 * @file OAuth2Session.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\Entities\User;
use Themes\RestApiTheme\Entities\OAuth2Client;
use Themes\RestApiTheme\Entities\OAuth2Scope;

/**
 * OAuth2Session store all information about OAuth2 scope.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\Core\Repositories\EntityRepository")
 * @ORM\Table(name="oauth_session")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "client_session" = "Themes\RestApiTheme\Entities\OAuth2ClientSession",
 *     "user_session" =  "Themes\RestApiTheme\Entities\OAuth2UserSession",
 * })
 */
abstract class OAuth2Session extends AbstractEntity
{

       abstract public function getOwner();

        /**
         * @ORM\OneToOne(targetEntity="Themes\RestApiTheme\Entities\OAuth2Client")
         * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
         **/
         private $client;

         /**
          * @return Themes\RestApiTheme\Entities\OAuth2Client
          */
         public function getClient() {
             return $this->client;
         }

         /**
          * @param Themes\RestApiTheme\Entities\OAuth2Client $clientId
          *
          * @return $this
          */
          public function setClient($client) {
              $this->client = $client;
              return $this;
          }

          /**
           * @ORM\ManyToMany(targetEntity="OAuth2Scope", inversedBy="sessions", cascade={"remove"})
           * @ORM\JoinTable(name="oauth_session_scope")
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

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
 * @file OAuth2RefreshToken.php
 * @author Maxime Constantinian
 */
 namespace Themes\RestApiTheme\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use Themes\RestApiTheme\Entities\OAuth2AccessToken;

/**
 * OAuth2RefreshToken store all information about auth code.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\Core\Repositories\EntityRepository")
 * @ORM\Table(name="oauth_refresh_token")
 */
class OAuth2RefreshToken extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    private $refreshToken;

    /**
     * @return string
     */
    public function getRefreshToken() {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     *
     * @return $this
     */
    public function setRefreshToken($refreshToken) {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @ORM\OneToOne(targetEntity="OAuth2AccessToken")
     * @ORM\JoinColumn(name="access_token_id", referencedColumnName="id", onDelete="CASCADE")
     **/
     private $accessToken;

     /**
      * @return Themes\RestApiTheme\Entities\OAuth2AccessToken
      */
     public function getAccessToken() {
         return $this->accessToken;
     }

     /**
      * @param Themes\RestApiTheme\Entities\OAuth2AccessToken $accessToken
      *
      * @return $this
      */
      public function setAccessToken($accessToken) {
          $this->accessToken = $accessToken;
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

}

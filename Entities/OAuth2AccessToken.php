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
 * @file OAuth2AccessToken.php
 * @author Maxime Constantinian
 */
namespace Themes\RestApiTheme\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Themes\RestApiTheme\AbstractEntities\AbstractValuedEntity;
use Themes\RestApiTheme\Entities\OAuth2Scope;
use Themes\RestApiTheme\Entities\OAuth2Session;

/**
 * OAuth2AccessToken store all information about access token.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\Core\Repositories\EntityRepository")
 * @ORM\Table(name="oauth_access_token")
 */
class OAuth2AccessToken extends AbstractValuedEntity
{
    /**
     * @ORM\OneToOne(targetEntity="OAuth2RefreshToken", inversedBy="accessToken")
     * @ORM\JoinColumn(name="refresh_token_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $refreshToken;

    /**
     * @return Themes\RestApiTheme\Entities\OAuth2RefreshToken
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param Themes\RestApiTheme\Entities\OAuth2RefreshToken $refreshToken
     *
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @ORM\OneToOne(targetEntity="Themes\RestApiTheme\Entities\OAuth2Session")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $session;

    /**
     * @return Themes\RestApiTheme\Entities\OAuth2Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Themes\RestApiTheme\Entities\OAuth2Session $session
     *
     * @return $this
     */
    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @ORM\Column(name="expire_time", type="datetime", nullable=false)
     * @var DateTime
     */
    private $expireTime;

    /**
     * @return DateTime
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * @param DateTime $expireTime
     *
     * @return $this
     */
    public function setExpireTime($expireTime)
    {
        $this->expireTime = $expireTime;
        return $this;
    }

    /**
     * @ORM\ManyToMany(targetEntity="OAuth2Scope", mappedBy="accessTokens")
     * @var ArrayCollection
     **/
    private $scopes;

    /**
     * @return ArrayCollection
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param OAuth2Scope $scope
     *
     * @return $this
     */
    public function addScope($scope)
    {
        $this->scopes->add($scope);
        return $this;
    }

    public function __construct()
    {
        $this->scopes = new ArrayCollection;
    }

    public function __toString()
    {
        return 'OAuth2AccessToken: id = ' . $this->getId() . ', value = ' . $this->getValue();
    }
}

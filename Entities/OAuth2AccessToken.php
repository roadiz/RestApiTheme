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

/**
 * OAuth2AccessToken store all information about access token.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\Core\Repositories\EntityRepository")
 * @ORM\Table(name="oauth_access_token")
 */
class OAuth2AccessToken extends AbstractValuedEntity
{
    /**
     * @ORM\OneToOne(targetEntity="Themes\RestApiTheme\Entities\OAuth2RefreshToken", mappedBy="accessToken")
     **/
    private $refreshToken = null;

    /**
     * @return OAuth2RefreshToken
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @ORM\OneToOne(targetEntity="Themes\RestApiTheme\Entities\OAuth2Session")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $session = null;

    /**
     * @return OAuth2Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param OAuth2Session $session
     *
     * @return $this
     */
    public function setSession($session)
    {
        $this->session = $session;
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
     * @return $this
     */
    public function addScope(OAuth2Scope $scope)
    {
        if (!$this->scopes->contains($scope)) {
            $this->scopes->add($scope);
        }
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

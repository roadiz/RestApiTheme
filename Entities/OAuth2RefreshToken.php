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

use Doctrine\ORM\Mapping as ORM;
use Themes\RestApiTheme\AbstractEntities\AbstractValuedEntity;

/**
 * OAuth2RefreshToken store all information about auth code.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\Core\Repositories\EntityRepository")
 * @ORM\Table(name="oauth_refresh_token")
 */
class OAuth2RefreshToken extends AbstractValuedEntity
{

    /**
     * @ORM\OneToOne(targetEntity="Themes\RestApiTheme\Entities\OAuth2AccessToken", inversedBy="refreshToken")
     * @ORM\JoinColumn(name="access_token_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $accessToken = null;

    /**
     * @return OAuth2AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param OAuth2AccessToken $accessToken
     *
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function __toString()
    {
        return 'OAuth2RefreshToken: id = ' . $this->getId() . ', value = ' . $this->getValue();
    }

}

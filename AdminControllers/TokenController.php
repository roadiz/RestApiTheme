<?php
/**
 * Copyright (c) 2017.
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
 * @file TokenController.php
 * @author ambroisemaupate
 *
 */

namespace Themes\RestApiTheme\AdminControllers;

use RZ\Roadiz\Core\ListManagers\EntityListManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Themes\RestApiTheme\Entities\OAuth2AccessToken;
use Themes\RestApiTheme\RestApiThemeApp;
use Themes\Rozier\RozierApp;

class TokenController extends RozierApp
{
    public function listAction(Request $request)
    {
        $listManager = new EntityListManager(
            $request,
            $this->get('em'),
            'Themes\RestApiTheme\Entities\OAuth2AccessToken',
            [],
            array(
                'id' => 'DESC'
            )
        );

        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['tokens'] = $listManager->getEntities();

        return $this->render(
            'admin/token/list.html.twig',
            $this->assignation,
            null,
            RestApiThemeApp::getThemeDir()
        );
    }

    /**
     * Return an deletion form for requested scope.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $tokenId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $tokenId)
    {
        /** @var OAuth2AccessToken $token */
        $token = $this->get('em')
            ->find('Themes\RestApiTheme\Entities\OAuth2AccessToken', (int)$tokenId);

        if ($token === null) {
            return $this->throw404();
        }

        $form = $this->buildDeleteForm($token);
        $form->handleRequest($request);

        if ($form->isValid() &&
            $form->getData()['tokenId'] == $token->getId()
        ) {
            $this->get('em')->remove($token);
            $this->get('em')->flush();
            $msg = $this->getTranslator()->trans(
                'oauth.token.%name%.deleted',
                array('%name%' => $token->getValue())
            );
            $this->publishConfirmMessage($request, $msg);
            /*
             * Force redirect to avoid resending form when refreshing page
             */
            $response = new RedirectResponse(
                $this->get('urlGenerator')->generate('tokenAdminListPage')
            );
            $response->prepare($request);

            return $response->send();
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('admin/token/delete.html.twig', $this->assignation, null, RestApiThemeApp::getThemeDir());
    }

    /**
     * @param OAuth2AccessToken $token
     * @return \Symfony\Component\Form\Form
     */
    protected function buildDeleteForm(OAuth2AccessToken $token)
    {
        $builder = $this->get('formFactory')
            ->createBuilder('form')
            ->add('tokenId', 'hidden', array(
                'data' => $token->getId(),
                'constraints' => array(
                    new NotBlank()
                )
            ));

        return $builder->getForm();
    }
}

<?php
/**
 * Copyright © 2015, Ambroise Maupate and Julien Blanchet
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
 *
 * @file LoginController.php
 * @author Ambroise Maupate
 */

namespace Themes\RestApiTheme\Controllers;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApiLoginController extends ApiController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $this->prepareApiServer();
        /** @var Session $session */
        $session = $this->get('session');
        $authParams = $this->server->getGrantType('authorization_code')->checkAuthorizeParams();

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $response = new RedirectResponse(
                $this->get('urlGenerator')->generate('authorizeScopePage', [
                    'client_id' => $authParams['client']->getId(),
                    'redirect_uri' => $authParams['redirect_uri'],
                    'response_type' => $authParams['response_type'],
                ])
            );

            $response->prepare($request);
            return $response;

        } else {

            $form = $this->buildLoginForm($authParams);
            $this->assignation['form'] = $form->createView();

            // get the login error if there is one
            if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
                $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
            } else {
                $error = $session->get(Security::AUTHENTICATION_ERROR);
                $session->remove(Security::AUTHENTICATION_ERROR);
            }

            $this->assignation['error'] = $error;

            return $this->render('login/login.html.twig', $this->assignation);
        }
    }

    /**
     * @param array $authParams
     * @return \Symfony\Component\Form\Form
     */
    private function buildLoginForm(array $authParams)
    {
        $defaults = [];

        $builder = $this->get('formFactory')
            ->createNamedBuilder(null, 'form', $defaults, [])
            ->add('_username', 'text', [
                'label' => $this->getTranslator()->trans('username'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('_password', 'password', [
                'label' => $this->getTranslator()->trans('password'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('_target_path', 'hidden', [
                'data' => $this->get('urlGenerator')->generate('authorizeScopePage', [
                    'client_id' => $authParams['client']->getId(),
                    'redirect_uri' => $authParams['redirect_uri'],
                    'response_type' => $authParams['response_type'],
                ])
            ]);

        return $builder->getForm();
    }
}

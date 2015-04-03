<?php
/*
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
 *
 * @file LoginController.php
 * @author Ambroise Maupate
 */

namespace Themes\RestApiTheme\Controllers;

use RZ\Roadiz\Utils\MediaFinders\SplashbasePictureFinder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints\NotBlank;
use Themes\Rozier\Controllers\LoginController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ApiLoginController extends LoginController
{
    /**
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $session = $this->getService('session');
        $authParams = $session->get('authParams');

        $user = $this->getService("securityContext")->getToken()->getUser();

        if ($user) {

            // Everything is okay, save $authParams to the a session and redirect the user to sign-in
            $session->set('authParams', $authParams);

            $response = new RedirectResponse(
                $this->getService('urlGenerator')->generate('authorizeScopePage')
            );

            $response->setStatusCode(302);

            return $response;

        } else {
            $form = $this->buildLoginForm();

            $this->assignation['form'] = $form->createView();

            // get the login error if there is one
            if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
                $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
            } else {
                $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
                $session->remove(SecurityContext::AUTHENTICATION_ERROR);
            }

            $this->assignation['error'] = $error;

            return $this->render('login/login.html.twig', $this->assignation);
        }
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function buildLoginForm()
    {
        $defaults = [];

        $builder = $this->getService('formFactory')
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
                            'data' => $this->getService('urlGenerator')->generate('authorizeScopePage')
                        ]);

        return $builder->getForm();
    }
}

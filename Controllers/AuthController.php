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
 * @file AuthController.php
 * @author Maxime Constantinian
 */

namespace Themes\RestApiTheme\Controllers;

use Themes\RestApiTheme\Storages;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthController extends ApiController
{
    public function oauthAction(Request $request) {
        $method = $request->getMethod();

        switch ($method) {
            case "GET":
                break;
            default:
                return $this->sendJson(500, ['error' => "Method not allowed.",
                                             "message" => 'Calling method ' . $method . ' is not allowed.']);
        }

        $this->prepareApiServer();

        try {
            $authParams = $this->server->getGrantType('authorization_code')->checkAuthorizeParams();
        } catch (\Exception $e) {
            if ($e->shouldRedirect()) {
                // Everything is okay, save $authParams to the a session and redirect the user to sign-in
                $reponse = new RedirectResponse(
                    $e->getRedirectUri()
                );

                $reponse->setStatusCode(302);

                return $reponse;
            }

            return $this->sendJson($e->httpStatusCode,
                [
                    'error'     =>  $e->errorType,
                    'message'   =>  $e->getMessage(),
                ]);
        }

        // Everything is okay, save $authParams to the a session and redirect the user to sign-in
        $session = $this->getService('session');
        $session->set('authParams', $authParams);
        $reponse = new RedirectResponse(
            $this->getService('urlGenerator')->generate(
                'signInPage'
            )
        );

        $reponse->setStatusCode(302);

        return $reponse;
    }

    public function authorizeAction(Request $request) {

        $this->prepareApiServer();
        $user = $this->getService("securityContext")->getToken()->getUser();

        $session = $this->getService('session');
        $authParams = $session->get('authParams');
        $builder = $this->getService('formFactory')
                        ->createBuilder()
                        ->add('approve', 'submit', [
                            'label' => $this->getTranslator()->trans('api.scope.approve')
                        ])
                        ->add('cancel', 'submit', [
                            'label' => $this->getTranslator()->trans('api.scope.cancel')
                        ]);

        $form = $builder->getForm();
        $form->handleRequest();

        if ($form->isValid()) {
            if ($form->get("approve")->isClicked()) {
                $redirectUri = $this->server->getGrantType('authorization_code')->newAuthorizeRequest('user', $user->getId(), $authParams);

                $reponse = new RedirectResponse(
                    $redirectUri
                );

                $reponse->setStatusCode(200);

            } else {
                $error = new \League\OAuth2\Server\Util\AccessDeniedException;

                $redirectUri = new \League\OAuth2\Server\Util\RedirectUri(
                    $authParams['redirect_uri'],
                    [
                        'error' =>  $error->errorType,
                        'message'   =>  $e->getMessage()
                    ]
                );

                $reponse = new RedirectResponse(
                    $redirectUri
                );

                $reponse->setStatusCode(302);
            }

            return $reponse;
        }

        $this->assignation['scopes'] = $authParams['scopes'];
        $this->assignation['form'] = $form->createView();

        return
            $this->render('scopeValidate.html.twig', $this->assignation, null);
    }

    public function accessTokenAction(Request $request) {
        $method = $request->getMethod();

        switch ($method) {
            case "POST":
                break;
            default:
                return $this->sendJson(500, ['error' => "Method not allowed.",
                                             "message" => 'Calling method ' . $method . ' is not allowed.']);
        }

        $this->prepareApiServer();

        // try {
            $response = $this->server->issueAccessToken();
            return $this->sendJson(200, $response);
        //
        // } catch (\Exception $e) {
        //     return $this->sendJson($e->httpStatusCode, [
        //         'error'     =>  $e->errorType,
        //         'message'   =>  $e->getMessage()
        //     ]);
        // }
    }
}

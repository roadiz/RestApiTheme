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
 * @file AuthController.php
 * @author Maxime Constantinian
 */
namespace Themes\RestApiTheme\Controllers;

use Doctrine\ORM\ORMException;
use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\Exception\OAuthException;
use League\OAuth2\Server\Util\RedirectUri;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends ApiController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|RedirectResponse
     */
    public function oauthAction(Request $request)
    {
        $method = $request->getMethod();

        switch ($method) {
            case "GET":
                break;
            default:
                return $this->json([
                    'error' => "Method not allowed.",
                    "message" => 'Calling method ' . $method . ' is not allowed.'
                ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $this->prepareApiServer();

        try {
            $authParams = $this->server->getGrantType('authorization_code')->checkAuthorizeParams();
        } catch (OAuthException $e) {
            if ($e->shouldRedirect()) {
                $response = new RedirectResponse(
                    $e->getRedirectUri()
                );
                $response->prepare($request);
                return $response;
            }

            $this->get("logger")->warning($e->getMessage());

            return $this->json([
                    'error' => $e->errorType,
                    'message' => $e->getMessage(),
                ], $e->httpStatusCode);
        }

        $response = new RedirectResponse(
            $this->get('urlGenerator')->generate('signInPage', [
                'client_id' => $authParams['client']->getId(),
                'redirect_uri' => $authParams['redirect_uri'],
                'response_type' => $authParams['response_type'],
            ])
        );

        $response->prepare($request);
        return $response;
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function authorizeAction(Request $request)
    {
        $this->validateAccessForRole('ROLE_USER');
        $this->prepareApiServer();
        $user = $this->getUser();

        $authParams = $this->server->getGrantType('authorization_code')->checkAuthorizeParams();
        /** @var FormBuilder $builder */
        $builder = $this->get('formFactory')->createBuilder();
        $builder->setAction($this->get('urlGenerator')->generate('authorizeScopePage', [
            'client_id' => $authParams['client']->getId(),
            'redirect_uri' => $authParams['redirect_uri'],
            'response_type' => $authParams['response_type'],
        ]));
        $builder->add('approve', 'submit', [
                'attr' => ['class' => 'uk-button uk-button-primary'],
                'label' => $this->getTranslator()->trans('api.scope.approve'),
            ])
            ->add('cancel', 'submit', [
                'attr' => ['class' => 'uk-button'],
                'label' => $this->getTranslator()->trans('api.scope.cancel'),
            ]);
        /** @var Form $form */
        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get("approve")->isClicked()) {
                $redirectUri = $this->server->getGrantType('authorization_code')->newAuthorizeRequest(
                    'user',
                    $user->getId(),
                    $authParams
                );

                $response = new RedirectResponse(
                    $redirectUri
                );
            } else {
                $error = new AccessDeniedException();
                $this->get("logger")->warning($error->getMessage());
                $redirectUri = new RedirectUri(
                    $authParams['redirect_uri'],
                    [
                        'error' => $error->errorType,
                        'message' => $error->getMessage(),
                    ]
                );

                $response = new RedirectResponse(
                    $redirectUri
                );

                $response->setStatusCode(Response::HTTP_FOUND);
            }
            $response->prepare($request);
            return $response;
        }

        $this->assignation['scopes'] = $authParams['scopes'];
        $this->assignation['form'] = $form->createView();

        return $this->render('scopeValidate.html.twig', $this->assignation, null, 'RestApiTheme');
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function accessTokenAction(Request $request)
    {
        $this->prepareApiServer();

        try {
            $response = $this->server->issueAccessToken();
            return $this->json($response);

        } catch (ORMException $e) {
            return $this->json([
                'error' => '\Doctrine\ORM\ORMException',
                'error_description' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (OAuthException $e) {
            return $this->json([
                'error' => $e->errorType,
                'error_description' => $e->getMessage()
            ], $e->httpStatusCode);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'General exception',
                'error_description' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

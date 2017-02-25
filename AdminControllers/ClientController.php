<?php
/**
 * Copyright (c) 2017. Ambroise Maupate and Julien Blanchet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
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
 * @file ClientController.php
 * @author Ambroise Maupate <ambroise@rezo-zero.com>
 */

namespace Themes\RestApiTheme\AdminControllers;

use RZ\Roadiz\Core\Exceptions\EntityAlreadyExistsException;
use RZ\Roadiz\Core\ListManagers\EntityListManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Themes\RestApiTheme\Entities\OAuth2Client;
use Themes\RestApiTheme\RestApiThemeApp;
use Themes\Rozier\RozierApp;

class ClientController extends RozierApp
{
    public function listAction(
        Request $request
    ) {
        $listManager = new EntityListManager(
            $request,
            $this->get('em'),
            'Themes\RestApiTheme\Entities\OAuth2Client',
            [],
            [
              'id' => 'DESC'
            ]
        );

        $listManager->handle();
        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['clients'] = $listManager->getEntities();

        return $this->render('admin/client/list.html.twig', $this->assignation, null, RestApiThemeApp::getThemeDir());
    }

    /**
     * Handle client creation pages.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $form = $this->get('formFactory')
            ->createBuilder()
            ->add(
                'name',
                'text',
                array(
                    'label' => $this->getTranslator()->trans('name'),
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            )
            ->add(
                'redirectUri',
                'text',
                array(
                    'label' => $this->getTranslator()->trans('redirect.uri'),
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            )
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $client = $this->createClient($form->getData());
                $msg = $this->getTranslator()->trans(
                    'oauth.client.%name%.created',
                    array('%name%'=>$client->getName())
                );
                $this->publishConfirmMessage($request, $msg);

                $response = new RedirectResponse(
                    $this->get('urlGenerator')->generate(
                        'clientAdminListPage'
                    )
                );
                $response->prepare($request);

                return $response->send();
            } catch (EntityAlreadyExistsException $e) {
                $this->publishErrorMessage($request, $e->getMessage());

                $response = new RedirectResponse(
                    $this->get('urlGenerator')->generate(
                        'clientAdminAddPage'
                    )
                );
                $response->prepare($request);

                return $response;
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('admin/client/add.html.twig', $this->assignation, null, RestApiThemeApp::getThemeDir());
    }

    private function createClient($data)
    {
        $client = new OAuth2Client();
        $client->setName($data['name']);
        $client->setRedirectUri($data['redirectUri']);
        $client->setClientId(md5(uniqid($data['name'], true)));
        $client->setClientSecret(md5(md5(uniqid($data['name'], true))));

        $this->get('em')->persist($client);
        $this->get('em')->flush();
        return $client;
    }

    public function editAction(Request $request, $clientId)
    {
        /** @var OAuth2Client $client */
        $client = $this->get('em')->find('Themes\RestApiTheme\Entities\OAuth2Client', $clientId);
        if ($client === null) {
            return $this->throw404();
        }
        $form = $this->get('formFactory')
            ->createBuilder()
            ->add(
                'name',
                'text',
                array(
                    'data' => $client->getName(),
                    'label' => $this->getTranslator()->trans('name'),
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            )
            ->add(
                'redirectUri',
                'text',
                array(
                    'data' => $client->getRedirectUri(),
                    'label' => $this->getTranslator()->trans('redirect.uri'),
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            )
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $client->setName($data['name']);
            $client->setRedirectUri($data['redirectUri']);

            $this->get('em')->flush();

            $msg = $this->getTranslator()->trans(
                'client.%name%.updated',
                array('%name%'=>$client->getName())
            );

            $this->publishConfirmMessage($request, $msg);

            $response = new RedirectResponse(
                $this->get('urlGenerator')->generate(
                    'clientAdminListPage'
                )
            );
            $response->prepare($request);

            return $response->send();
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['client'] = $client;

        return $this->render('admin/client/edit.html.twig', $this->assignation, null, RestApiThemeApp::getThemeDir());
    }

    /**
     * Return an deletion form for requested client.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int                                      $clientId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $clientId)
    {
        /** @var OAuth2Client $client */
        $client = $this->get('em')
            ->find('Themes\RestApiTheme\Entities\OAuth2Client', (int) $clientId);

        if ($client === null) {
            return $this->throw404();
        }

        $form = $this->buildDeleteForm($client);
        $form->handleRequest($request);

        if ($form->isValid() &&
            $form->getData()['clientId'] == $client->getId()) {
            $this->get('em')->remove($client);
            $this->get('em')->flush();
            $msg = $this->getTranslator()->trans(
                'client.%name%.deleted',
                array('%name%'=>$client->getName())
            );
            $this->publishConfirmMessage($request, $msg);
            /*
             * Force redirect to avoid resending form when refreshing page
             */
            $response = new RedirectResponse(
                $this->get('urlGenerator')->generate('clientAdminListPage')
            );
            $response->prepare($request);

            return $response;
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('admin/client/delete.html.twig', $this->assignation, null, RestApiThemeApp::getThemeDir());
    }

    /**
     * @param OAuth2Client $client
     * @return \Symfony\Component\Form\Form
     *
     */
    protected function buildDeleteForm(OAuth2Client $client)
    {
        $builder = $this->get('formFactory')
            ->createBuilder('form')
            ->add('clientId', 'hidden', array(
                'data' => $client->getId(),
                'constraints' => array(
                    new NotBlank()
                )
            ));

        return $builder->getForm();
    }
}

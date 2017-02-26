<?php

namespace Themes\RestApiTheme\AdminControllers;

use RZ\Roadiz\Core\Exceptions\EntityAlreadyExistsException;
use RZ\Roadiz\Core\ListManagers\EntityListManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Themes\RestApiTheme\Entities\OAuth2Scope;
use Themes\RestApiTheme\Forms\OAuth2ScopeType;
use Themes\RestApiTheme\RestApiThemeApp;
use Themes\Rozier\RozierApp;

class ScopeController extends RozierApp
{
    public function listAction(Request $request)
    {
        $listManager = new EntityListManager(
            $request,
            $this->get('em'),
            'Themes\RestApiTheme\Entities\OAuth2Scope',
            [],
            array(
                'id' => 'DESC'
            )
        );

        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['scopes'] = $listManager->getEntities();

        return $this->render('admin/scope/list.html.twig', $this->assignation, null, RestApiThemeApp::getThemeDir());
    }

    /**
     * Handle scope creation pages.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $scope = new OAuth2Scope();
        $form = $this->createForm(new OAuth2ScopeType($this->get('em')), $scope);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $this->get('em')->persist($scope);
                $this->get('em')->flush();

                $msg = $this->getTranslator()->trans(
                    'oauth.scope.%name%.created',
                    array('%name%' => $scope->getName())
                );
                $this->publishConfirmMessage($request, $msg);

                $response = new RedirectResponse(
                    $this->get('urlGenerator')->generate(
                        'scopeAdminListPage'
                    )
                );
                $response->prepare($request);

                return $response->send();
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['form'] = $form->createView();

        return $this->render(
            'admin/scope/add.html.twig',
            $this->assignation,
            null,
            RestApiThemeApp::getThemeDir()
        );
    }

    /**
     * @param Request $request
     * @param $scopeId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $scopeId)
    {
        /** @var OAuth2Scope $scope */
        $scope = $this->get('em')->find('Themes\RestApiTheme\Entities\OAuth2Scope', $scopeId);
        if ($scope === null) {
            return $this->throw404();
        }
        $form = $this->createForm(new OAuth2ScopeType($this->get('em')), $scope);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $this->get('em')->flush();

                $msg = $this->getTranslator()->trans(
                    'oauth.scope.%name%.updated',
                    array('%name%' => $scope->getName())
                );
                $this->publishConfirmMessage($request, $msg);
                $response = new RedirectResponse(
                    $this->get('urlGenerator')->generate(
                        'scopeAdminListPage'
                    )
                );
                $response->prepare($request);

                return $response->send();
            } catch (EntityAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['scope'] = $scope;

        return $this->render('admin/scope/edit.html.twig', $this->assignation, null, RestApiThemeApp::getThemeDir());
    }

    /**
     * Return an deletion form for requested scope.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $scopeId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $scopeId)
    {
        $scope = $this->get('em')
            ->find('Themes\RestApiTheme\Entities\OAuth2Scope', (int)$scopeId);

        if ($scope === null) {
            return $this->throw404();
        }

        $form = $this->buildDeleteForm($scope);
        $form->handleRequest($request);

        if ($form->isValid() &&
            $form->getData()['scopeId'] == $scope->getId()
        ) {
            $this->get('em')->remove($scope);
            $this->get('em')->flush();
            $msg = $this->getTranslator()->trans(
                'oauth.scope.%name%.deleted',
                array('%name%' => $scope->getName())
            );
            $this->publishConfirmMessage($request, $msg);
            /*
             * Force redirect to avoid resending form when refreshing page
             */
            $response = new RedirectResponse(
                $this->get('urlGenerator')->generate('scopeAdminListPage')
            );
            $response->prepare($request);

            return $response->send();
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('admin/scope/delete.html.twig', $this->assignation, null, RestApiThemeApp::getThemeDir());
    }

    /**
     * @param OAuth2Scope $scope
     * @return \Symfony\Component\Form\Form
     */
    protected function buildDeleteForm(OAuth2Scope $scope)
    {
        $builder = $this->get('formFactory')
            ->createBuilder('form')
            ->add('scopeId', 'hidden', array(
                'data' => $scope->getId(),
                'constraints' => array(
                    new NotBlank()
                )
            ));

        return $builder->getForm();
    }
}

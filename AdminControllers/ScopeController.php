<?php

namespace Themes\RestApiTheme\AdminControllers;

use Themes\Rozier\RozierApp;

use Themes\RestApiTheme\Entities\OAuth2Scope;

use RZ\Roadiz\Core\ListManagers\EntityListManager;
use RZ\Roadiz\Utils\StringHandler;
use RZ\Roadiz\Utils\Security\PasswordGenerator;

use RZ\Roadiz\Core\Exceptions\EntityAlreadyExistsException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Validator\Constraints\NotBlank;

class ScopeController extends RozierApp
{

    public function listAction(
        Request $request
    ) {

        $listManager = new EntityListManager(
            $request,
            $this->getService("em"),
            "Themes\RestApiTheme\Entities\OAuth2Scope",
            [],
            array(
              "id" => "DESC"
            )
        );

        $listManager->handle();

        $this->assignation['filters'] = $listManager->getAssignation();
        $this->assignation['scopes'] = $listManager->getEntities();

        //$this->getService('stopwatch')->start('twigRender');

        return $this->render('admin/scope/list.html.twig', $this->assignation, null,
                             \Themes\RestApiTheme\RestApiThemeApp::getThemeDir());
    }

    /**
     * Handle scope creation pages.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        //$this->validateAccessForRole('ROLE_ACCESS_NEWS');

        $form = $this->getService('formFactory')
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
                'description',
                'text',
                array(
                    'label' => $this->getTranslator()->trans('description'),
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            )
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $scope = $this->createScope($form->getData());

                $msg = $this->getTranslator()->trans(
                    'oauth.scope.%name%.created',
                    array('%name%'=>$scope->getName())
                );
                $this->publishConfirmMessage($request, $msg);

                $response = new RedirectResponse(
                    $this->getService('urlGenerator')->generate(
                        'scopeAdminListPage'
                    )
                );
                $response->prepare($request);

                return $response->send();
            } catch (EntityAlreadyExistsException $e) {
                $this->publishErrorMessage($request, $e->getMessage());

                $response = new RedirectResponse(
                    $this->getService('urlGenerator')->generate(
                        'scopeAdminAddPage'
                    )
                );
                $response->prepare($request);

                return $response->send();
            }
        }

        $this->assignation['form'] = $form->createView();

        return
            $this->render('admin/scope/add.html.twig', $this->assignation, null,
                          \Themes\RestApiTheme\RestApiThemeApp::getThemeDir());
    }

    private function createScope($data)
    {
        $scope = new OAuth2Scope();
        $scope->setName($data["name"]);
        $scope->setDescription($data["description"]);

        $this->getService("em")->persist($scope);
        $this->getService("em")->flush();
        return $scope;
    }

    public function editAction(Request $request, $scopeId)
    {
        //$this->validateAccessForRole('ROLE_ACCESS_NEWS');

        $scope =  $this->getService("em")->find("Themes\RestApiTheme\Entities\OAuth2Scope", $scopeId);
        if ($scope === null) {
            return $this->throw404();
        }
        $form = $this->getService('formFactory')
            ->createBuilder()
            ->add(
                'name',
                'text',
                array(
                    'data' => $scope->getName(),
                    'label' => $this->getTranslator()->trans('name'),
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            )
            ->add(
                'description',
                'text',
                array(
                    'data' => $scope->getRedirectUri(),
                    'label' => $this->getTranslator()->trans('description'),
                    'constraints' => array(
                        new NotBlank()
                    )
                )
            )
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $scope->setName($data['name']);
            $scope->setDescription($data['description']);

            $this->getService('em')->flush();

            $msg = $this->getTranslator()->trans(
                'scope.%name%.updated',
                array('%name%'=>$scope->getName())
            );

            $this->publishConfirmMessage($request, $msg);

            $response = new RedirectResponse(
                $this->getService('urlGenerator')->generate(
                    'scopeAdminListPage'
                )
            );
            $response->prepare($request);

            return $response->send();
        }

        $this->assignation['form'] = $form->createView();
        $this->assignation['scope'] = $scope;

        return $this->render('admin/scope/edit.html.twig', $this->assignation, null,
                             \Themes\RestApiTheme\RestApiThemeApp::getThemeDir());
    }

    /**
     * Return an deletion form for requested scope.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param int                                      $scopeId
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $scopeId)
    {
        //$this->validateAccessForRole('ROLE_ACCESS_NEWS_DELETE');

        $scope = $this->getService('em')
            ->find('Themes\RestApiTheme\Entities\OAuth2Scope', (int) $scopeId);

        if ($scope === null) {
            return $this->throw404();
        }

        $form = $this->buildDeleteForm($scope);
        $form->handleRequest($request);

        if ($form->isValid() &&
            $form->getData()['scopeId'] == $scope->getId()) {
            $this->getService('em')->remove($scope);
            $this->getService('em')->flush();
            $msg = $this->getTranslator()->trans(
                'scope.%name%.deleted',
                array('%name%'=>$scope->getName())
            );
            $this->publishConfirmMessage($request, $msg);
            /*
             * Force redirect to avoid resending form when refreshing page
             */
            $response = new RedirectResponse(
                $this->getService('urlGenerator')->generate('scopeAdminListPage')
            );
            $response->prepare($request);

            return $response->send();
        }

        $this->assignation['form'] = $form->createView();

        return $this->render('admin/scope/delete.html.twig', $this->assignation, null,
                             \Themes\RestApiTheme\RestApiThemeApp::getThemeDir());
    }

    /**
     * @param RZ\Roadiz\Core\Entities\Node $node
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function buildDeleteForm(OAuth2Scope $scope)
    {
        $builder = $this->getService('formFactory')
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

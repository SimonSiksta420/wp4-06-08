<?php

namespace App\Presenters;

use App\Model\UserFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;

final class SignPresenter extends Nette\Application\UI\Presenter
{
    private UserFacade $userFacade;

    public function __construct(UserFacade $userFacade)
    {
        $this->userFacade = $userFacade;
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte své uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    public function signInFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            $this->getUser()->login($data->username, $data->password);
            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení bylo úspěšné.');
        $this->redirect('Homepage:');
    }

    protected function createComponentSignUpForm(): Form
    {
        $form = new Form;

        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím vyplňte své uživatelské jméno.');

        $form->addEmail('email', 'Email:')
            ->setRequired('Prosím vyplňte svůj email.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo.');

        $form->addSubmit('send', 'Registrovat');

        $form->onSuccess[] = [$this, 'signUpFormSucceeded'];
        return $form;
    }

    public function signUpFormSucceeded(\stdClass $data): void
    {
        $this->userFacade->addUser($data->username, $data->email, $data->password);
        $this->flashMessage('Registrace byla úspěšná.');
        $this->getUser()->login($data->username, $data->password);
        $this->redirect('Homepage:');
    }

    protected function createComponentChangeUserForm(): Form
    {
        $user = $this->getUser()->getIdentity();

        $form = new Form;

        $form->addText('username', 'Uživatelské jméno:')
            ->setDefaultValue($user->data['username'])
            ->setRequired('Prosím vyplňte své uživatelské jméno.');

        $form->addEmail('email', 'Email:')
            ->setDefaultValue($user->data['email'])
            ->setRequired('Prosím vyplňte svůj email.');

        $form->addPassword('password', 'Heslo:');

        $form->addSubmit('send', 'Změnit');


        $form->onSuccess[] = [$this, 'changeUserFormSucceeded'];
        return $form;
    }

    public function changeUserFormSucceeded(\stdClass $data): void
    {
        bdump($data);
        $this->userFacade->updateUser($this->getUser()->getId(), $data->username, $data->email, $data->password);
        $this->flashMessage('Změna účtu byla úspěšná.');
        if ($data->password != '') {
            $this->redirect('Sign:out');
        } else {
            
            //$this->getUser()->login($data->username, $this->getUser()->getIdentity()->data['password']);
            $this->redirect('Sign:out');
        }
    }
}

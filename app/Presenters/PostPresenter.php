<?php

namespace App\Presenters;

use App\Model\PostFacade;
use Nette;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\UI\Form;

final class PostPresenter extends Nette\Application\UI\Presenter
{

	private PostFacade $facade;

	public function __construct(PostFacade $facade)
	{
		$this->facade = $facade;
	}

	public function renderShow(int $postId): void
	{
		$post = $this->facade
			->getPostById($postId);


		$this->facade->addView($postId);

		$this->template->post = $post;
		$this->template->comments = $this->facade->getComments($postId);
		$this->template->like = $this->facade->getUserRating($postId, $this->user->id);
	}

	public function actionShow(int $postId): void
	{
		if (!$this->getUser()->IsLoggedIn() && $this->facade->getPostById($postId)->status == 'ARCHIVED') {
			$this->flashMessage('Jsi gay a nemůžeš vidět tento archivovaný příspěvěk!');
			$this->redirect('Homepage:');
		} else {
		}
	}

	public function handleLike(int $postId, int $like)
	{
		$this->redrawControl('like');
		if ($this->getUser()->isLoggedIn()) {}
		
		$userId = $this->getUser()->getId();
		$this->facade->updateRating($userId, $postId, $like);
	}

	protected function createComponentCommentForm(): Form
	{
		$form = new Form;

		$form->addText('name', 'Jméno:')
			->setRequired();

		$form->addEmail('email', 'E-mail:');

		$form->addTextArea('content', 'Komentář:')
			->setRequired();

		$form->addSubmit('send', 'Publikovat komentář');

		$form->onSuccess[] = [$this, 'commentFormSucceeded'];

		return $form;
	}

	public function commentFormSucceeded(\stdClass $data): void
	{
		$postId = $this->getParameter('postId');

		$this->facade->addComment($postId, $data);

		$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}
}

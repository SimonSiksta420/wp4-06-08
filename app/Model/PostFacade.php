<?php

namespace App\Model;

use Nette;
use stdClass;

final class PostFacade
{
	use Nette\SmartObject;

	private Nette\Database\Explorer $database;

	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}

	public function getPublicArticles()
	{
		return $this->database
			->table('posts')
			->where('created_at < ', new \DateTime)
			->order('created_at DESC');
	}

	public function getPostById(int $postId)
	{
		$post = $this->database
			->table('posts')
			->get($postId);

		if (!$post) {
			$this->error('Post not found');
		}
		return $post;
	}

	public function addComment(int $postId, \stdClass $data)
	{

		$this->database->table('comments')->insert([
			'post_id' => $postId,
			'name' => $data->name,
			'email' => $data->email,
			'content' => $data->content,
		]);
		return;
	}

	public function getComments(int $postId)
	{
		return $this->database
			->table('comments')
			->where('post_id', $postId);
	}

	public function editPost(int $postId, array $data)
	{
		$post = $this->database
			->table('posts')
			->get($postId);
		$post->update($data);
		return $post;
	}

	public function insertPost(array $data)
	{
		$post = $this->database
			->table('posts')
			->insert($data);
		return $post;
	}

	public function addView(int $postId)
	{
		$currentViews = $this->database
			->table('posts')
			->get($postId)
			->views_count;
		$currentViews++;

		bdump($currentViews);
		$data['views_count'] = $currentViews;

		$this->database
			->table('posts')
			->get($postId)
			->update($data);
	}

	public function getUserRating(int $postId, int $userId)
	{
		$like = $this->database
			->table('rating')
			->where([
				'user_id' => $userId,
				'post_id' => $postId,
			]);

		if ($like->count() == 0) {
			return null;
		}
		return $like->fetch()->like_value;
	}

	public function updateRating(int $userId, int $postId, int $like)
	{
		$currentRating = $this->database
			->table('rating')
			->get([
				'user_id' => $userId,
				'post_id' => $postId,
			]);

		if ($currentRating != null) {
			$this->database
				->query('UPDATE rating SET like_value = ? WHERE user_id = ? AND post_id = ?', $like, $userId, $postId);
		} else {
			$this->database
				->table('rating')
				->insert([
					'user_id' => $userId,
					'post_id' => $postId,
					'like' => $like
				]);
		}
	}

	public function deletePost(int $postId)
	{
		$this->database
			->table('posts')
			->get($postId)
			->delete();
	}
}

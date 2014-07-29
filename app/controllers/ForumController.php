<?php

use Illuminate\Support\Str;

/**
 * Gestion du forum
 *
 */
class ForumController extends BaseController {

	/**
	 * Affiche la page d'accueil du forum
	 *
	 */
	public function index()
	{
		$categories = Forum::where('parent_id', '=', 0)->orderBy('position', 'ASC')->get();
		return View::make('forum.index', array('categories' => $categories));
	}

	/**
	 * Affiche la catégorie demandé
	 *
	 * @access public
	 * @param $slug Slug de la catégorie
	 * @param $id Id de la catégorie
	 * @return void
	 */
	public function category($slug, $id)
	{
		$category = Forum::find($id);
		if($category->getPermission()->show_forum != true)
		{
			return Redirect::route('forum_index')->with('message', 'You haven\'t access to this category');
		}
		return View::make('forum.category', array('c' => $category));
	}

	/**
	 * Affiche le forums et les topics à l'intérieur
	 *
	 *
	 */
	public function display($slug, $id)
	{
		$forum = Forum::find($id);
		if($forum->parent_id == 0)
		{
			return Redirect::route('forum_category', array('slug' => $forum->slug, 'id' => $forum->id));
		}
		$category = Forum::find($forum->parent_id);
		// Permission
		if($category->getPermission()->show_forum != true)
		{
			return Redirect::route('forum_index')->with('message', 'You haven\'t access to this forum');
		}
		$topics = $forum->topics()->orderBy('created_at', 'DESC')->paginate();

		return View::make('forum.display', array('forum' => $forum, 'topics' => $topics, 'category' => $category));
	}

	/**
	 * Affiche le topic
	 *
	 *
	 */
	public function topic($slug, $id)
	{
		$topic = Topic::find($id);
		$forum = $topic->forum;
		$category = $forum->getCategory();
		$posts = $topic->posts;

		// L'utilisateur possède le droit de crée un topic ici
		if($category->getPermission()->read_topic != true)
		{
			return Redirect::route('forum_index')->with('message', 'You can\'t read this topic');
		}

		$topic->views++;
		$topic->save();

		return View::make('forum.topic', array('topic' => $topic, 'forum' => $forum, 'category' => $category, 'posts' => $posts));
	}

	/**
	 * Ajoute une réponse à un topic
	 *
	 * @param $slug Slug du topic
	 * @param $id Id du topic
	 */
	public function reply($slug, $id)
	{
		$user = Auth::user();
		$topic = Topic::find($id);
		$forum = $topic->forum;
		$category = $forum->getCategory();

		// L'utilisateur possède le droit de crée un topic ici
		if($category->getPermission()->reply_topic != true)
		{
			return Redirect::route('forum_index')->withm('message', 'You can\'t reply this topic');
		}

		$post = new Post();
		$post->content = Input::get('content');
		$post->user_id = $user->id;
		$post->topic_id = $topic->id;

		$v = Validator::make($post->toArray(), array(
			'content' => 'required',
			'user_id' => 'required',
			'topic_id' => 'required'
			)
		);
		if($v->passes())
		{
			$post->save();

			$topic->last_post_user_id = $user->id;
			$topic->last_post_user_username = $user->username;
			$topic->num_post = Post::where('topic_id', '=', $topic->id)->count();
			$topic->save();

			/** Compte les topics dans ce forum */
			$forum->num_post = $forum->getPostCount($forum->id);
			$forum->num_topic = $forum->getTopicCount($forum->id);
			$forum->save();

			return Redirect::route('forum_topic', array('slug' => $topic->slug, 'id' => $topic->id));
		}
		else
		{
		}
	}

	/**
	 * Crée un nouveau topic dans le forum désiré
	 *
	 * @param $slug Slug du forum dans lequel sera le topic
	 * @param $id Id du forum dans lequel sera le topic
	 */
	public function newTopic($slug, $id)
	{
		$user = Auth::user();
		$forum = Forum::find($id);
		$category = $forum->getCategory();
		$parsedContent = null;

		// L'utilisateur possède le droit de crée un topic ici
		if($category->getPermission()->start_topic != true)
		{
			return Redirect::route('forum_index')->with('message', 'You can\'t start a new topic here');
		}

		// Prévisualisation du post
		if(Request::getMethod() == 'POST' && Input::get('preview') == true)
		{
			$code = new Decoda\Decoda(Input::get('content'));
			$code->defaults();
			$parsedContent = $code->parse();
		}

		if(Request::getMethod() == 'POST' && Input::get('post') == true)
		{
			// Crée le topic
			$topic = new Topic();
			$topic->name = Input::get('title');
			$topic->slug = Str::slug(Input::get('title'));
			$topic->state = "open";
			$topic->first_post_user_id = $user->id;
			$topic->first_post_user_username = $user->username;
			$topic->last_post_user_id = $user->id;
			$topic->last_post_user_username = $user->username;
			$topic->views = 0;
			$topic->pinned = false;
			$topic->forum_id = $forum->id;
			$v = Validator::make($topic->toArray(), $topic->rules);
			if($v->passes())
			{
				$topic->save();

				$post = new Post();
				$post->content = Input::get('content');
				$post->user_id = $user->id;
				$post->topic_id = $topic->id;
				$v = Validator::make($post->toArray(), $post->rules);
				if($v->passes())
				{
					$post->save();
					$topic->num_post = 1;
					$topic->save();
					$forum->num_topic = $forum->getTopicCount($forum->id);
					$forum->num_post = $forum->getPostCount($forum->id);
					$forum->last_topic_id = $topic->id;
					$forum->last_topic_name = $topic->name;
					$forum->last_topic_slug = $topic->slug;
					$forum->last_topic_user_id = $user->id;
					$forum->last_topic_user_username = $user->username;
					$forum->save();
					return Redirect::route('forum_topic', array('slug' => $topic->slug, 'id' => $topic->id));
				}
				else
				{
					// Impoossible de save le premier post donc delete le topic
					$topic->delete();
				}
			}
			else
			{

			}
		}
		return View::make('forum.new_topic', array('forum' => $forum, 'category' => $category, 'parsedContent' => $parsedContent, 'title' => Input::get('title'), 'content' => Input::get('content')));
	}
} ?>

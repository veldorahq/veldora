<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Post;
use Veldora\Framework\Http\Response;
use Veldora\Framework\View\Engine;

class HomeController
{
    /**
     * Create a new HomeController instance with autowired View Engine.
     */
    public function __construct(protected Engine $view)
    {
    }

    /**
     * Render the homepage template with SQLite database inputs.
     */
    public function index(): Response
    {
        $post = Post::find(1);
        $postTitle = $post ? $post->title : 'No posts found';
        $postBody = $post ? $post->body : 'Run migration and seeder to create content.';

        // Render view template
        $html = $this->view->render('home', [
            'postTitle' => $postTitle,
            'postBody' => $postBody,
        ]);

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
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
     * Render the homepage template.
     */
    public function index(): Response
    {
        $postTitle = 'Welcome to Veldora';
        $postBody  = 'The modern PHP framework you actually own. Build blazing-fast applications with zero configuration.';

        try {
            $post = Post::find(1);
            if ($post) {
                $postTitle = $post->title;
                $postBody  = $post->body;
            }
        } catch (\Throwable) {
            // Migrations not yet executed, fallback to starter greeting
        }

        // Render view template
        $html = $this->view->render('home', [
            'postTitle' => $postTitle,
            'postBody'  => $postBody,
        ]);

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
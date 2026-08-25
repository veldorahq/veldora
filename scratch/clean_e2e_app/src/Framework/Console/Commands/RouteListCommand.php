<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Http\Router;

class RouteListCommand extends Command
{
    protected static ?string $defaultName = 'route:list';

    protected function configure(): void
    {
        $this
            ->setName('route:list')
            ->setDescription('List all registered routes');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $router = $app->get(Router::class);

        // Load web routes
        $routesFile = $app->routesPath('web.php');
        if (file_exists($routesFile)) {
            require_once $routesFile;
        }

        // Get routes via reflection or getter
        $ref = new \ReflectionClass($router);
        $routesProp = $ref->getProperty('routes');
        $routesProp->setAccessible(true);
        $routes = $routesProp->getValue($router);

        echo "\n\033[35m\033[1m  ▲ Veldora Route List\033[0m\n\n";

        if (empty($routes)) {
            echo "  \033[90mNo routes registered.\033[0m\n\n";
            return;
        }

        printf("  %-10s %-32s %-36s %-16s\n", "\033[1mMethod\033[0m", "\033[1mURI\033[0m", "\033[1mAction\033[0m", "\033[1mMiddleware\033[0m");
        echo "  " . str_repeat('─', 96) . "\n";

        foreach ($routes as $route) {
            $methods = implode('|', $route->getMethods());
            $uri = $route->getUri();
            $action = $route->getAction();

            if (is_array($action)) {
                $actionStr = $action[0] . '@' . $action[1];
            } elseif ($action instanceof \Closure) {
                $actionStr = 'Closure';
            } else {
                $actionStr = (string)$action;
            }

            $middleware = implode(', ', $route->getMiddleware()) ?: '—';

            $methodColor = match ($methods) {
                'GET' => "\033[32mGET\033[0m",
                'POST' => "\033[33mPOST\033[0m",
                'PUT', 'PATCH' => "\033[34m" . $methods . "\033[0m",
                'DELETE' => "\033[31mDELETE\033[0m",
                default => "\033[36m" . $methods . "\033[0m",
            };

            printf("  %-19s %-32s %-36s \033[90m%-16s\033[0m\n", $methodColor, $uri, $actionStr, $middleware);
        }

        echo "\n  \033[90mTotal routes:\033[0m \033[1m" . count($routes) . "\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}

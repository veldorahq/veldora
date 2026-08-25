<?php

declare(strict_types=1);

namespace Veldora\Framework\View;

use InvalidArgumentException;
use RuntimeException;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Database\Connection;

class Engine
{
    /**
     * The compiled views cache directory.
     */
    protected string $cachePath;

    /**
     * The layout to extend.
     */
    protected ?string $layout = null;

    /**
     * The layout section contents.
     *
     * @var array<string, string>
     */
    protected array $sections = [];

    /**
     * The active section stack.
     *
     * @var array<string>
     */
    protected array $sectionStack = [];

    /**
     * The active components stack.
     *
     * @var array<array{name: string, attributes: array<string, mixed>, slots: array<string, string>}>
     */
    protected array $componentStack = [];

    /**
     * The active slot name.
     */
    protected string $currentSlot = 'default';

    /**
     * The compiler instance.
     */
    protected Compiler $compiler;

    /**
     * Create a new Engine instance.
     */
    public function __construct(protected Application $app)
    {
        $this->cachePath = $this->app->storagePath('framework/views');
        $this->compiler = new Compiler();
    }

    /**
     * Render a view file with extracted variables.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): string
    {
        return $this->renderView($view, $data);
    }

    /**
     * Render an explicit view template file path.
     *
     * @param array<string, mixed> $data
     */
    public function renderFile(string $filePath, array $data = []): string
    {
        $compiledFile = $this->compileIfNeeded($filePath);

        ob_start();
        $this->evaluatePath($compiledFile, $data);
        $output = ob_get_clean();

        return $output !== false ? $output : '';
    }

    /**
     * Compile and render raw Veldora template string.
     *
     * @param array<string, mixed> $data
     */
    public function renderString(string $template, array $data = []): string
    {
        $compiled = $this->compiler->compile($template);
        
        $tempFile = $this->cachePath . '/' . md5($template) . '.php';
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
        file_put_contents($tempFile, $compiled);

        ob_start();
        $this->evaluatePath($tempFile, $data);
        $output = ob_get_clean();

        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        return $output !== false ? $output : '';
    }

    /**
     * Internal view rendering routine with layout isolation.
     *
     * @param array<string, mixed> $data
     */
    protected function renderView(string $view, array $data = []): string
    {
        $viewFile = $this->findViewFile($view);
        $compiledFile = $this->compileIfNeeded($viewFile);

        // Capture output
        ob_start();

        $this->evaluatePath($compiledFile, $data);

        $output = ob_get_clean();
        if ($output === false) {
            $output = '';
        }

        // Handle layouts if one was defined during view execution
        if ($this->layout !== null) {
            $layout = $this->layout;
            $this->layout = null; // reset layout
            return $this->renderView($layout, $data);
        }

        return $output;
    }

    /**
     * Find the absolute path for a view identifier.
     */
    protected function findViewFile(string $view): string
    {
        $normalized = str_replace('.', '/', $view);
        $viewsPath = $this->app->basePath('resources/views');

        $paths = [
            $viewsPath . '/' . $normalized . '.veldora.php',
            $viewsPath . '/' . $normalized . '.veldora',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new InvalidArgumentException("View [{$view}] not found.");
    }

    /**
     * Compile a template if modified or uncached.
     */
    protected function compileIfNeeded(string $viewFile): string
    {
        if (!is_dir($this->cachePath) && !mkdir($this->cachePath, 0755, true) && !is_dir($this->cachePath)) {
            throw new RuntimeException("Failed to create view cache directory [{$this->cachePath}]");
        }

        $cacheFile = $this->cachePath . '/' . md5($viewFile) . '.php';

        // Recompile in local development or if cache is stale
        $isLocalDev = $this->app->has(Connection::class); // Simple indicator for local run environment
        
        clearstatcache();
        if (!file_exists($cacheFile) || filemtime($viewFile) > filemtime($cacheFile)) {
            $content = file_get_contents($viewFile) ?: '';
            $compiled = $this->compiler->compile($content);
            file_put_contents($cacheFile, $compiled);
        }

        return $cacheFile;
    }

    /**
     * Register target layout to extend.
     */
    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Start recording content for a section, or set inline content directly.
     */
    public function startSection(string $name, ?string $content = null): void
    {
        if ($content !== null) {
            $this->sections[$name] = (string) $content;
            return;
        }

        $this->sectionStack[] = $name;
        ob_start();
    }

    /**
     * Stop recording content for the active section.
     */
    public function endSection(): void
    {
        if (empty($this->sectionStack)) {
            throw new RuntimeException('Cannot end section without starting one.');
        }

        $name = array_pop($this->sectionStack);
        $content = ob_get_clean();
        
        $this->sections[$name] = $content === false ? '' : $content;
    }

    /**
     * Output content of a registered layout section with optional default fallback.
     */
    public function yieldSection(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Output content of a nested partial view.
     *
     * @param array<string, mixed> $data
     */
    public function include(string $view, array $data = []): string
    {
        return $this->renderView($view, $data);
    }

    /**
     * Start rendering a component.
     *
     * @param array<string, mixed> $attributes
     */
    public function startComponent(string $name, array $attributes = []): void
    {
        $this->componentStack[] = [
            'name' => $name,
            'attributes' => $attributes,
            'slots' => [],
        ];
    }

    /**
     * Start capturing content for a component slot.
     */
    public function startSlot(string $name): void
    {
        $this->currentSlot = $name;
        ob_start();
    }

    /**
     * Stop capturing content for the active component slot.
     */
    public function endSlot(): void
    {
        $content = ob_get_clean();
        if ($content === false) {
            $content = '';
        }

        if (!empty($this->componentStack)) {
            $lastIndex = count($this->componentStack) - 1;
            $this->componentStack[$lastIndex]['slots'][$this->currentSlot] = $content;
        }
    }

    /**
     * Render the active component from the stack.
     */
    public function renderCurrentComponent(): string
    {
        if (empty($this->componentStack)) {
            throw new RuntimeException('No component active to render.');
        }

        $component = array_pop($this->componentStack);
        
        return $this->renderComponent($component['name'], $component['attributes'], $component['slots']);
    }

    /**
     * Render a custom component by resolving its class or template.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, string> $slots
     */
    protected function renderComponent(string $name, array $attributes, array $slots): string
    {
        // 1. Try finding a custom PHP component class
        $studly = str_replace(' ', '', ucwords(str_replace(['.', '-'], ['\\', ' '], $name)));
        $class = 'App\\Components\\Ui\\' . $studly;

        if (class_exists($class)) {
            /** @var mixed $component */
            $component = $this->app->get($class);
            
            // Set properties if supported
            if (method_exists($component, 'mount')) {
                $component->mount($attributes);
            } else {
                foreach ($attributes as $key => $value) {
                    if (property_exists($component, $key)) {
                        $component->$key = $value;
                    }
                }
            }
            
            $component->slot = $slots['default'] ?? '';
            $component->slots = $slots;

            if (method_exists($component, 'render')) {
                return (string) $component->render($this);
            }
        }

        // 2. Render as a template component
        $templatePath = $this->findComponentTemplate($name);
        
        $data = array_merge($attributes, $slots);
        $data['slot'] = $slots['default'] ?? '';

        $compiledFile = $this->compileIfNeeded($templatePath);

        ob_start();
        $this->evaluatePath($compiledFile, $data);

        $output = ob_get_clean();
        return $output === false ? '' : $output;
    }

    /**
     * Scan directories to locate component template files.
     */
    protected function findComponentTemplate(string $name): string
    {
        $normalized = str_replace('.', '/', $name);
        $studly = str_replace(' ', '', ucwords(str_replace(['.', '-'], ['/', ' '], $name)));

        // Paths ordered by lookup priority
        $paths = [
            $this->app->basePath('app/components/ui/' . $studly . '.veldora.php'),
            $this->app->basePath('app/components/ui/' . $studly . '.veldora'),
            $this->app->basePath('app/Components/ui/' . $studly . '.veldora.php'),
            $this->app->basePath('app/Components/ui/' . $studly . '.veldora'),
            $this->app->basePath('resources/views/components/' . $normalized . '.veldora.php'),
            $this->app->basePath('resources/views/components/' . $normalized . '.veldora'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new InvalidArgumentException("Component template [{$name}] not found.");
    }

    /**
     * Evaluate a compiled view file in isolated scope with $this bound.
     *
     * @param array<string, mixed> $__data
     */
    protected function evaluatePath(string $__file, array $__data): void
    {
        extract($__data, EXTR_SKIP);
        require $__file;
    }
}

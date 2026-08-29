<?php

declare(strict_types=1);

namespace Veldora\Framework\View;

class Compiler
{
    /**
     * Compile view template contents to plain PHP.
     */
    public function compile(string $content): string
    {
        // 1. Compile escapes and raws
        $content = $this->compileEscapes($content);
        
        // 2. Compile control directives
        $content = $this->compileDirectives($content);

        // 3. Compile components & slots
        $content = $this->compileComponents($content);

        return $content;
    }

    /**
     * Compile {{ }} and {!! !!} syntax tags.
     */
    protected function compileEscapes(string $content): string
    {
        // Raw output
        $content = (string) preg_replace('/\{!!\s*(.*?)\s*!!\}/s', '<?php echo $1; ?>', $content);

        // Escaped output
        $content = (string) preg_replace('/\{\{\s*(.*?)\s*\}\}/s', '<?php echo htmlspecialchars((string) ($1 ?? \'\'), ENT_QUOTES, \'UTF-8\'); ?>', $content);

        return $content;
    }

    /**
     * Compile structural directives (@if, @foreach, @extends, @csrf etc.).
     */
    protected function compileDirectives(string $content): string
    {
        // @csrf → hidden input with csrf_token()
        $content = (string) preg_replace(
            '/@csrf/',
            '<?php echo \'<input type="hidden" name="_token" value="\' . csrf_token() . \'">\'; ?>',
            $content
        );

        // @method('PUT') / @method('DELETE') / @method('PATCH')
        $content = (string) preg_replace_callback('/@method\s*\((.*?)\)/s', function (array $matches) {
            $val = trim($matches[1], "'\" ");
            return "<?php echo '<input type=\"hidden\" name=\"_method\" value=\"' . strtoupper('{$val}') . '\">'; ?>";
        }, $content);

        // @auth / @endauth
        $content = (string) preg_replace('/@auth/', '<?php if(auth()->check()): ?>', $content);
        $content = (string) preg_replace('/@endauth/', '<?php endif; ?>', $content);

        // @guest / @endguest
        $content = (string) preg_replace('/@guest/', '<?php if(auth()->guest()): ?>', $content);
        $content = (string) preg_replace('/@endguest/', '<?php endif; ?>', $content);

        // @admin / @endadmin
        $content = (string) preg_replace('/@admin/', '<?php if(auth()->isAdmin()): ?>', $content);
        $content = (string) preg_replace('/@endadmin/', '<?php endif; ?>', $content);

        // Conditionals (nested parentheses via PCRE recursive patterns)
        $content = (string) preg_replace_callback('/@if\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $cond = substr($matches[1], 1, -1);
            return "<?php if({$cond}): ?>";
        }, $content);

        $content = (string) preg_replace_callback('/@elseif\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $cond = substr($matches[1], 1, -1);
            return "<?php elseif({$cond}): ?>";
        }, $content);

        $content = (string) preg_replace('/@else/', '<?php else: ?>', $content);
        $content = (string) preg_replace('/@endif/', '<?php endif; ?>', $content);

        // @unless / @endunless
        $content = (string) preg_replace_callback('/@unless\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $cond = substr($matches[1], 1, -1);
            return "<?php if(!({$cond})): ?>";
        }, $content);
        $content = (string) preg_replace('/@endunless/', '<?php endif; ?>', $content);

        // @foreach / @endforeach
        $content = (string) preg_replace_callback('/@foreach\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $cond = substr($matches[1], 1, -1);
            return "<?php foreach({$cond}): ?>";
        }, $content);
        $content = (string) preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);

        // @forelse / @empty / @endforelse
        $content = (string) preg_replace_callback('/@forelse\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $expr = substr($matches[1], 1, -1);
            preg_match('/^\s*(.*?)\s+as\s+/i', $expr, $m);
            $array = $m[1] ?? '$__arr';
            return "<?php if(!empty({$array})): foreach({$expr}): ?>";
        }, $content);
        $content = (string) preg_replace('/@empty/', '<?php endforeach; else: ?>', $content);
        $content = (string) preg_replace('/@endforelse/', '<?php endif; ?>', $content);

        // @for / @endfor
        $content = (string) preg_replace_callback('/@for\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $cond = substr($matches[1], 1, -1);
            return "<?php for({$cond}): ?>";
        }, $content);
        $content = (string) preg_replace('/@endfor/', '<?php endfor; ?>', $content);

        // @while / @endwhile
        $content = (string) preg_replace_callback('/@while\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $cond = substr($matches[1], 1, -1);
            return "<?php while({$cond}): ?>";
        }, $content);
        $content = (string) preg_replace('/@endwhile/', '<?php endwhile; ?>', $content);

        // @php / @endphp
        $content = (string) preg_replace('/@php/', '<?php ', $content);
        $content = (string) preg_replace('/@endphp/', ' ?>', $content);

        // @dump
        $content = (string) preg_replace_callback('/@dump\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $expr = substr($matches[1], 1, -1);
            return "<?php var_dump({$expr}); ?>";
        }, $content);

        // Layouts and includes
        $content = (string) preg_replace_callback('/@extends\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $expr = substr($matches[1], 1, -1);
            return "<?php \$this->extend({$expr}); ?>";
        }, $content);

        $content = (string) preg_replace_callback('/@section\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $expr = substr($matches[1], 1, -1);
            return "<?php \$this->startSection({$expr}); ?>";
        }, $content);

        $content = (string) preg_replace('/@endsection/', '<?php \$this->endSection(); ?>', $content);

        $content = (string) preg_replace_callback('/@yield\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $expr = substr($matches[1], 1, -1);
            return "<?php echo \$this->yieldSection({$expr}); ?>";
        }, $content);

        $content = (string) preg_replace_callback('/@include\s*(\((?:[^()]++|(?1))*\))/s', function (array $matches) {
            $expr = substr($matches[1], 1, -1);
            return "<?php echo \$this->renderView({$expr}, get_defined_vars()); ?>";
        }, $content);

        return $content;
    }


    /**
     * Compile component tags <x-...> and <x-slot...>.
     */
    protected function compileComponents(string $content): string
    {
        // 1. Match slot opening tag
        $content = (string) preg_replace('/<x-slot\s+name=["\']([^"\']*)["\']\s*>/s', '<?php $this->endSlot(); $this->startSlot(\'$1\'); ?>', $content);

        // 2. Match slot closing tag
        $content = (string) preg_replace('/<\/x-slot>/s', '<?php $this->endSlot(); $this->startSlot(\'default\'); ?>', $content);

        // 3. Match self-closing component tags first
        $content = (string) preg_replace_callback(
            '/<x-([a-zA-Z0-9_\-\.]+)([^>]*)\/>/s',
            fn(array $matches) => $this->compileSelfClosingComponent($matches[1], $matches[2]),
            $content
        );

        // 4. Match opening component tags
        $content = (string) preg_replace_callback(
            '/<x-([a-zA-Z0-9_\-\.]+)([^>]*)>/s',
            fn(array $matches) => $this->compileComponentStart($matches[1], $matches[2]),
            $content
        );

        // 5. Match closing component tags
        $content = (string) preg_replace('/<\/x-([a-zA-Z0-9_\-\.]+)>/s', '<?php $this->endSlot(); echo $this->renderCurrentComponent(); ?>', $content);

        return $content;
    }

    /**
     * Compile attribute parameters.
     */
    protected function compileComponentStart(string $name, string $attributesStr): string
    {
        $attrPairs = $this->parseAttributes($attributesStr);
        $attrsCode = '[' . implode(', ', $attrPairs) . ']';
        return "<?php \$this->startComponent('{$name}', {$attrsCode}); \$this->startSlot('default'); ?>";
    }

    /**
     * Compile self-closing components.
     */
    protected function compileSelfClosingComponent(string $name, string $attributesStr): string
    {
        $attrPairs = $this->parseAttributes($attributesStr);
        $attrsCode = '[' . implode(', ', $attrPairs) . ']';
        return "<?php \$this->startComponent('{$name}', {$attrsCode}); echo \$this->renderCurrentComponent(); ?>";
    }

    /**
     * Parse raw attribute syntax strings.
     *
     * @return array<string> Compiled PHP array segments.
     */
    protected function parseAttributes(string $attributesStr): array
    {
        preg_match_all('/([a-zA-Z0-9_\-\:]+)=["\']([^"\']*)["\']/', $attributesStr, $matches, PREG_SET_ORDER);
        $attrPairs = [];

        foreach ($matches as $match) {
            $key = $match[1];
            $val = $match[2];

            if (str_starts_with($key, ':')) {
                $realKey = substr($key, 1);
                $attrPairs[] = "'{$realKey}' => ({$val})";
            } else {
                $escapedVal = addslashes($val);
                $attrPairs[] = "'{$key}' => '{$escapedVal}'";
            }
        }

        return $attrPairs;
    }
}

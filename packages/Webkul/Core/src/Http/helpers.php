<?php

use Illuminate\Support\Facades\Vite;
use Stevebauman\Purify\Facades\Purify;
use Webkul\Core\Facades\Acl;
use Webkul\Core\Facades\Core;
use Webkul\Core\Facades\Menu;
use Webkul\Core\Facades\SystemConfig;

if (! function_exists('core')) {
    /**
     * Core helper.
     *
     * @return \Webkul\Core\Core
     */
    function core()
    {
        return Core::getFacadeRoot();
    }
}

if (! function_exists('menu')) {
    /**
     * Menu helper.
     *
     * @return \Webkul\Core\Menu
     */
    function menu()
    {
        return Menu::getFacadeRoot();
    }
}

if (! function_exists('acl')) {
    /**
     * Acl helper.
     *
     * @return \Webkul\Core\Acl
     */
    function acl()
    {
        return Acl::getFacadeRoot();
    }
}

if (! function_exists('system_config')) {
    /**
     * System Config helper.
     *
     * @return \Webkul\Core\SystemConfig
     */
    function system_config()
    {
        return SystemConfig::getFacadeRoot();
    }
}

if (! function_exists('clean_path')) {
    /**
     * Clean path.
     */
    function clean_path(string $path): string
    {
        return collect(explode('/', $path))
            ->filter(fn ($segment) => ! empty($segment))
            ->join('/');
    }
}

if (! function_exists('clean_content')) {
    /**
     * Clean content.
     */
    function clean_content(string $content): string
    {
        $cleaned = Purify::clean($content);

        $patterns = [
            '/\{\{.*?\}\}/',
            '/\{!!.*?!!\}/',
            '/@(php|if|else|endif|foreach|endforeach|for|endfor|while|endwhile|switch|endswitch|case|break|continue|include|extends|section|endsection|yield|push|endpush|stack|endstack)/',
            '/<\?php.*?\?>/s',
        ];

        foreach ($patterns as $pattern) {
            $cleaned = preg_replace($pattern, '', $cleaned);
        }

        $cleaned = str_replace(
            ['{{', '}}', '{!!', '!!}'],
            ['&#123;&#123;', '&#125;&#125;', '&#123;!!', '!!&#125;'],
            $cleaned
        );

        return $cleaned;
    }
}

if (! function_exists('array_permutation')) {
    function array_permutation($input)
    {
        $results = [];

        foreach ($input as $key => $values) {
            if (empty($values)) {
                continue;
            }

            if (empty($results)) {
                foreach ($values as $value) {
                    $results[] = [$key => $value];
                }
            } else {
                $append = [];

                foreach ($results as &$result) {
                    $result[$key] = array_shift($values);

                    $copy = $result;

                    foreach ($values as $item) {
                        $copy[$key] = $item;
                        $append[] = $copy;
                    }

                    array_unshift($values, $result[$key]);
                }

                $results = array_merge($results, $append);
            }
        }

        return $results;
    }
}

if (! function_exists('bagisto_asset')) {
    /**
     * Bagisto asset helper function.
     * Works without Theme module by using Vite directly.
     *
     * @param  string  $path
     * @param  string|null  $namespace
     * @return string
     */
    function bagisto_asset(string $path, ?string $namespace = null)
    {
        // If namespace is provided, use Vite with bagisto-vite config
        if ($namespace) {
            $viters = config('bagisto-vite.viters');

            if (empty($viters[$namespace])) {
                // Fallback to asset() if namespace not found
                return asset($path);
            }

            $viteUrl = trim($viters[$namespace]['package_assets_directory'], '/').'/'.$path;

            try {
                return Vite::useHotFile($viters[$namespace]['hot_file'])
                    ->useBuildDirectory($viters[$namespace]['build_directory'])
                    ->asset($viteUrl);
            } catch (\Exception $e) {
                // Fallback to asset() if Vite fails
                return asset($path);
            }
        }

        // If no namespace, default to 'admin' or use asset() as fallback
        $defaultNamespace = 'admin';
        $viters = config('bagisto-vite.viters');

        if (! empty($viters[$defaultNamespace])) {
            $viteUrl = trim($viters[$defaultNamespace]['package_assets_directory'], '/').'/'.$path;

            try {
                return Vite::useHotFile($viters[$defaultNamespace]['hot_file'])
                    ->useBuildDirectory($viters[$defaultNamespace]['build_directory'])
                    ->asset($viteUrl);
            } catch (\Exception $e) {
                // Fallback to asset() if Vite fails
                return asset($path);
            }
        }

        // Final fallback
        return asset($path);
    }
}

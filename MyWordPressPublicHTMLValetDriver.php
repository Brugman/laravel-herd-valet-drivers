<?php

namespace Valet\Drivers\Custom;

use Valet\Drivers\ValetDriver;

class MyWordPressPublicHTMLValetDriver extends ValetDriver
{
    /**
     * Determine if the driver serves the request.
     */
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return file_exists($sitePath.'/public_html/wp-config.php') || file_exists($sitePath.'/public_html/wp-config-sample.php');
    }

    /**
     * Take any steps necessary before loading the front controller for this driver.
     */
    public function beforeLoading(string $sitePath, string $siteName, string $uri): void
    {
        $_SERVER['PHP_SELF'] = $uri;
        $_SERVER['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
    }

    /**
     * Determine if the incoming request is for a static file.
     */
    public function isStaticFile(string $sitePath, string $siteName, string $uri)/* : string|false */
    {
        $filePath = $sitePath.'/public_html'.$uri;

        return $this->isActualFile($filePath) ? $filePath : false;
    }

    /**
     * Get the fully resolved path to the application's front controller.
     */
    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        $sitePath = $sitePath.'/public_html';

        $uri = $this->forceTrailingSlash($uri);
        $uri = rtrim($uri, '/');

        $candidates = [
            $sitePath.$uri,
            $sitePath.$uri.'/index.php',
            $sitePath.'/index.php',
            $sitePath.'/index.html',
        ];

        foreach ($candidates as $candidate) {
            if ($this->isActualFile($candidate)) {
                $_SERVER['SCRIPT_FILENAME'] = $candidate;
                $_SERVER['SCRIPT_NAME'] = str_replace($sitePath, '', $candidate);
                $_SERVER['DOCUMENT_ROOT'] = $sitePath;

                return $candidate;
            }
        }

        return null;
    }

    /**
     * Redirect to uri with trailing slash.
     */
    private function forceTrailingSlash($uri): ?string
    {
        if (substr($uri, -1 * strlen('/wp-admin')) == '/wp-admin') {
            header('Location: '.$uri.'/');
            exit;
        }

        return $uri;
    }
}


<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

// $app->withFacades();

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Register Manifest Parser
|--------------------------------------------------------------------------
|
| This function will be used in templates to get revisioned assets.
| These assets are versioned and uploaded to S3 through our gulp script.
|
*/
if (! function_exists('read_manifest')) {
    /**
     * Get the path to a versioned Elixir file.
     *
     * @param  string  $file
     * @param  string  $buildDirectory
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    function read_manifest($file, $buildDirectory = 'styles')
    {
        static $manifest;
        static $manifestPath;

        if (is_null($manifest) || $manifestPath !== $buildDirectory) {
            $manifest = json_decode(file_get_contents(public_path($buildDirectory.'/rev-manifest.json')), true);

            $manifestPath = $buildDirectory;
        }

        if (isset($manifest[$file])) {
            return '/' . trim($buildDirectory . '/' . $manifest[$file], '/');
        }

        throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
    }
}

/*
|--------------------------------------------------------------------------
| Register Public Path Helper
|--------------------------------------------------------------------------
|
| This function is in the Laravel Framework by default but not in Lumen.
| We need this function for the above revision manifest function to work properly.
|
*/
if (! function_exists('public_path')) {
    /**
     * Return the path to public dir
     *
     * @param  null $path
     * @return string
     */
    function public_path($path = null)
    {
            return rtrim(app()->basePath('public/' . $path), '/');
    }
}

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__.'/../app/Http/routes.php';
});

return $app;

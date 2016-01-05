<?php

namespace Chrisbjr\ApiGuard\Http\Middleware;

use App;
use Chrisbjr\ApiGuard\Repositories\ApiKeyRepository;
use Closure;
use Config;
use EllipseSynergie\ApiResponse\Laravel\Response;
use Exception;
use Illuminate\Support\Str;
use Input;
use League\Fractal\Manager;
use Log;
use Route;

class ApiGuardMiddleware
{
    /**
     * @var ApiKeyRepository
     */
    public $apiKey = null;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var Manager
     */
    public $manager;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Let's instantiate the response class first
        $this->manager = new Manager;

        $this->response = new Response($this->manager);

        // This is the actual request object used
        $request = Route::getCurrentRequest();

        // Let's get the method
        Str::parseCallback(Route::currentRouteAction(), null);

        $routeArray = Str::parseCallback(Route::currentRouteAction(), null);

        if (last($routeArray) == null) {
            // There is no method?
            return $this->response->errorMethodNotAllowed();
        }

        $method = last($routeArray);

        // We should check if key authentication is enabled for this method
        $keyAuthentication = true;

        if (isset($apiMethods[$method]['keyAuthentication']) && $apiMethods[$method]['keyAuthentication'] === false) {
            $keyAuthentication = false;
        }

        if ($keyAuthentication === true) {

            $key = $request->header(Config::get('apiguard.keyName', 'X-Authorization'));

            if (empty($key)) {
                // Try getting the key from elsewhere
                $key = Input::get(Config::get('apiguard.keyName', 'X-Authorization'));
            }

            if (empty($key)) {
                // It's still empty!
                return $this->response->errorUnauthorized();
            }

            $apiKeyModel = App::make(Config::get('apiguard.model', 'Chrisbjr\ApiGuard\Models\ApiKey'));

            if ( !$apiKeyModel instanceof ApiKeyRepository) {
                Log::error('[Chrisbjr/ApiGuard] You ApiKey model should be an instance of ApiKeyRepository.');
                throw new Exception("You ApiKey model should be an instance of ApiKeyRepository.");
            }

            $this->apiKey = $apiKeyModel->getByKey($key);

            if (empty($this->apiKey)) {
                return $this->response->errorUnauthorized();
            }

            // API key exists
            // Check level of API
            if ( ! empty($apiMethods[$method]['level'])) {
                if ($this->apiKey->level < $apiMethods[$method]['level']) {
                    return $this->response->errorForbidden();
                }
            }
        }

        return $next($request);
    }
}
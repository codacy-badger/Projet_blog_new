<?php

namespace App\utilities;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher
{

    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var Response
     */
    private $response;


    public function pipe($middleware)   /// permet de renseigner et ajouter un middleware. 
    {
        $this->middlewares[] = $middleware;
       $this->response = new Response(); 
       return $this; 
    }


    public function process( $request)
    { 
        $middleware = $this->getMiddleware(); 
        $this->index++;
        if (is_null($middleware)) {
            return $this->response;
        }
        if (is_callable($middleware)) {
            return $middleware($request, $this->response, [$this, 'process']);
        } elseif ($middleware) {
            return $middleware->process($request, $this);
        }
    }

    private function getMiddleware()
    {
        if (isset($this->middlewares[$this->index])) {
            return $this->middlewares[$this->index];
        }
        return null;
    }

}
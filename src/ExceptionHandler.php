<?php

namespace Jowy\ExceptionHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Whoops\Handler\HandlerInterface;
use Whoops\Run;
use Zend\Stratigility\MiddlewareInterface;
use Symfony\Component\HttpKernel\Exception\HttpException as RootHttpException;

/**
 * Class ExceptionHandler
 * @package Jowy\ExceptionHandler
 */
class ExceptionHandler implements MiddlewareInterface
{
    /**
     * @var Run
     */
    protected $whoops;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    protected $catch;

    /**
     * @param HandlerInterface $error_handler
     * @param LoggerInterface $logger
     * @param bool $catch
     */
    public function __construct(HandlerInterface $error_handler, LoggerInterface $logger, $catch = true)
    {
        $this->whoops = new Run();
        $this->whoops->pushHandler($error_handler);
        $this->logger = $logger;
        $this->catch = $catch;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        try {
            return $next($request, $response);
        } catch (\Exception $e) {
            /**
             * catch error?
             */
            if ($this->catch) {
                ob_start();
                $this->whoops->handleException($e);
                $body = ob_get_clean();
            } else {
                $body = "";
            }

            /**
             * log error
             */
            $this->logger->error($e->getMessage(), [
                $request->getUri()->getPath(),
                $request->getMethod(),
                $e->getFile(),
                $e->getLine(),
            ]);

            var_dump($e->getMessage());

            /**
             * Set header and status code if exception instance of Symfony HttpException
             */
            if ($e instanceof RootHttpException) {
                $response = $response->withStatus($e->getStatusCode(), $e->getMessage());

                foreach ($e->getHeaders() as $key => $value) {
                    $response = $response->withAddedHeader($key, $value);
                }
            } else {
                $response = $response->withStatus(500, "Internal Error");
            }

            /**
             * set response status code & body
             */
            $response->getBody()->write($body);

            return $response;
        }
    }
}

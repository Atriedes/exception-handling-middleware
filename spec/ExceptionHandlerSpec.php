<?php

namespace spec\Jowy\ExceptionHandler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Whoops\Handler\JsonResponseHandler;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class ExceptionHandlerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType("Jowy\\ExceptionHandler\\ExceptionHandler");
        $this->shouldImplement("Zend\\Stratigility\\MiddlewareInterface");
    }

    public function let(JsonResponseHandler $error_handler, LoggerInterface $logger)
    {
        $this->beConstructedWith($error_handler, $logger, true);
    }

    public function it_should_display_whoops()
    {
        $response = $this->__invoke(
            ServerRequestFactory::fromGlobals(),
            new Response(),
            function (ServerRequestInterface $req, ResponseInterface $res) {
                throw new NotFoundHttpException("Not Found");
            }
        );

        $response->shouldReturnAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBeLike(404);
    }

    public function it_should_return_with_http_header()
    {
        $response = $this->__invoke(
            ServerRequestFactory::fromGlobals(),
            new Response(),
            function (ServerRequestInterface $req, ResponseInterface $res) {
                throw new MethodNotAllowedHttpException(["POST"], "Method Not Allowed");
            }
        );

        $response->shouldReturnAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBeLike(405);
    }

    public function it_should_not_display_whoops(JsonResponseHandler $error_handler, LoggerInterface $logger)
    {
        $this->beConstructedWith($error_handler, $logger, false);

        $response = $this->__invoke(
            ServerRequestFactory::fromGlobals(),
            new Response(),
            function (ServerRequestInterface $req, ResponseInterface $res) {
                throw new NotFoundHttpException("Not Found");
            }
        );

        $response->shouldReturnAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBeLike(404);
    }

    public function it_should_display_internal_error()
    {
        $response = $this->__invoke(
            ServerRequestFactory::fromGlobals(),
            new Response(),
            function (ServerRequestInterface $req, ResponseInterface $res) {
                throw new \Exception("Internal Error");
            }
        );

        $response->shouldReturnAnInstanceOf(ResponseInterface::class);
        $response->getStatusCode()->shouldBeLike(500);
    }
}

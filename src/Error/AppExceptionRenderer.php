<?php
declare(strict_types=1);

namespace App\Error;

use App\Http\Exception\TooManyRequestsException;
use Cake\Error\Renderer\WebExceptionRenderer;
use Psr\Http\Message\ResponseInterface;

class AppExceptionRenderer extends WebExceptionRenderer
{
    /**
     * Renders the response for TooManyRequestsException.
     *
     * @param \App\Http\Exception\TooManyRequestsException $exception The exception to render.
     * @return \Psr\Http\Message\ResponseInterface The rendered response.
     */
    public function tooManyRequests(TooManyRequestsException $exception): ResponseInterface
    {
        $response = $this->controller->getResponse()->withStatus(429);
        if ($exception->getRetryAfter() !== null) {
            $response = $response->withHeader('Retry-After', (string)$exception->getRetryAfter());
        }
        $this->controller->setResponse($response);

        $this->controller->viewBuilder()
        ->setPlugin('DefaultTheme')
        ->setTemplate('Error/error429')
        ->setLayout('error');

        $viewVars = [
            'message' => $exception->getMessage(),
            'error' => $exception,
            'code' => $exception->getCode(),
            'url' => $this->controller->getRequest()->getRequestTarget(),
            'retryAfter' => $exception->getRetryAfter(),
            'exceptions' => [$exception],
        ];

        $this->controller->set($viewVars);

        return $this->_outputMessage('error429');
    }

    /**
     * Renders a generic error page for other types of exceptions.
     *
     * @return \Psr\Http\Message\ResponseInterface The rendered response.
     */
    public function render(): ResponseInterface
    {
        $response = parent::render();

        // Add any custom headers or modifications to the response here
        if ($response instanceof ResponseInterface) {
            $exception = $this->error;
            $response = $response->withHeader('X-Error-Type', get_class($exception));
        }

        return $response;
    }
}

<?php
declare(strict_types=1);

namespace App\Error;

use App\Http\Exception\TooManyRequestsException;
use Cake\Error\Renderer\WebExceptionRenderer;
use Cake\Http\Response;
use Throwable;

class AppExceptionRenderer extends WebExceptionRenderer
{
    /**
     * Renders the response for the exception.
     *
     * @return \Cake\Http\Response The response to be sent.
     */
    public function render(): Response
    {
        $exception = $this->error;
        $code = $this->getHttpCode($exception);

        if ($exception instanceof TooManyRequestsException) {
            return $this->renderTooManyRequests($exception);
        }

        return $this->renderDefaultError($exception, $code);
    }

    /**
     * Renders the response for TooManyRequestsException.
     *
     * @param \Throwable $exception The exception to render.
     * @return \Cake\Http\Response The response to be sent.
     */
    protected function renderTooManyRequests(Throwable $exception): Response
    {
        $code = 429;
        $response = $this->controller->getResponse()->withStatus($code);
        $this->controller->setResponse($response);
        $this->controller->viewBuilder()->setTemplate('error429');

        $this->controller->set([
            'message' => $exception->getMessage(),
            'url' => $this->controller->getRequest()->getRequestTarget(),
            'error' => $exception,
            'code' => $code,
            'exceptions' => [$exception], // Add this line back
        ]);

        return $this->_outputMessage('error429');
    }

    /**
     * Renders the response for default errors.
     *
     * @param \Throwable $exception The exception to render.
     * @param int $code The HTTP status code.
     * @return \Cake\Http\Response The response to be sent.
     */
    protected function renderDefaultError(Throwable $exception, int $code): Response
    {
        $this->controller->set([
            'message' => $exception->getMessage(),
            'url' => $this->controller->getRequest()->getRequestTarget(),
            'error' => $exception,
            'code' => $code,
            'exceptions' => [$exception], // Add this line back
        ]);

        return parent::render();
    }
}
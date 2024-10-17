<?php
declare(strict_types=1);

namespace App\Error;

use Cake\Error\Renderer\WebExceptionRenderer;
use App\Http\Exception\TooManyRequestsException;
use Psr\Http\Message\ResponseInterface;

class AppExceptionRenderer extends WebExceptionRenderer
{
    public function render(): ResponseInterface
    {
        $exception = $this->error;
        $code = $this->getHttpCode($exception);

        if ($exception instanceof TooManyRequestsException) {
            $this->controller->setResponse($this->controller->getResponse()->withStatus($code));
            $this->controller->viewBuilder()->setTemplatePath('Error');
            
            $message = $exception->getMessage();
            $url = $this->controller->getRequest()->getRequestTarget();
            
            $this->controller->set([
                'message' => $message,
                'url' => h($url),
                'error' => $exception,
                'code' => $code,
                'exceptions' => [$exception],
                //'trace' => Debugger::formatTrace($exception->getTrace(), ['format' => 'array']),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            
            $this->controller->viewBuilder()->setOption('serialize', ['message', 'url', 'code', 'exceptions', 'file', 'line']);
            
            return $this->_outputMessage('error429');
        }

        return parent::render();
    }
}
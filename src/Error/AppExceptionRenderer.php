<?php
declare(strict_types=1);

namespace App\Error;

use App\Http\Exception\TooManyRequestsException;
use Cake\Error\Renderer\WebExceptionRenderer;

class AppExceptionRenderer extends WebExceptionRenderer
{
    public function render(): \Cake\Http\Response
    {
        $exception = $this->error;
        $code = $this->getHttpCode($exception);

        if ($exception instanceof TooManyRequestsException) {
            $code = 429;
            $this->controller->setResponse($this->controller->getResponse()->withStatus($code));
            $this->controller->viewBuilder()->setTemplate('error429');
            
            // Set variables for the error template
            $this->controller->set([
                'message' => $exception->getMessage(),
                'error' => $exception,
                'code' => $code,
                'exceptions' => [$exception], // Add this line
            ]);
            
            return $this->_outputMessage('error429');
        }

        // For other exceptions, make sure to set the 'exceptions' variable
        $this->controller->set([
            'message' => $exception->getMessage(),
            'error' => $exception,
            'code' => $code,
            'exceptions' => [$exception],
        ]);

        return parent::render();
    }
}
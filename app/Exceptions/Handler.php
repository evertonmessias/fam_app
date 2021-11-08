<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Support\Facades\Storage;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        ////////////////////////////////////////////
        // Univestibular Handler
        global $UNI_GLOBAL_UNIQUE_ERROR_ID;

        // Setamos true para identificar na parte de render que este erro precisa ser logado
        $UNI_GLOBAL_UNIQUE_ERROR_ID = true;

        ////////////////////////////////////////////
        // Laravel Handler

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // Login
        if ($exception->getMessage() == 'Unauthenticated.')
            return $this->unauthenticated($request, $exception);

        ////////////////////////////////////////////
        // Univestibular Handler
        global $UNI_GLOBAL_UNIQUE_ERROR_ID;

        ////////////////////////////////////////////
        // Código HTTP
        if (method_exists($exception, 'getStatusCode'))
            $rCode = $exception->getStatusCode();
        else
            $rCode = 500;

        // Quando for 404, jogar para o Laravel
        if ($rCode == 404) {
            try {
                $html = view('errors.' . $rCode, ['exception' => $exception]);
                return response($html, $rCode);
            }
            catch (\Exception $e) {
                return parent::render($request, $exception);
            }
        }

        ////////////////////////////////////////////
        // Gerar código único de erro e report
        if ($UNI_GLOBAL_UNIQUE_ERROR_ID === true) {
            // Função helper
            $base64url = function ($data) { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); };

            // Diretório de logs
            $logdir = '/error-logs';
            if (!Storage::exists($logdir))
                Storage::makeDirectory($logdir);

            $files = Storage::files($logdir);
            $count = count ($files);
            $count = str_pad($count, 6, '0', STR_PAD_LEFT);
            $ueid = $base64url($count . rand(0, 9));

            // Criar ID único do erro, que usaremos para salvar o arquivo
            $UNI_GLOBAL_UNIQUE_ERROR_ID = $ueid . '.' . $rCode;

            $_SEP_ = str_pad('', 48, '#');

            // Criar log
            $logfile = [];
            $logfile[] = $_SEP_;
            $logfile[] = '# Log #' . $count . ' (ID: ' . $ueid . ')';
            $logfile[] = '# Timestamp: ' . date('Y-m-d H:i:s');
            $logfile[] = '# Status code: ' . $rCode;
            $logfile[] = '# Message: ' . $exception->getMessage();

            // Request
            $logfile[] = $_SEP_;
            $logfile[] = '# Request URI: ' . $request->getRequestUri();
            $logfile[] = '# Request Headers:';
            $logfile[] = (string) $request->headers;

            // Logar input()
            $logfile[] = $_SEP_;
            $logfile[] = '# Request Input: ';

            foreach ($request->all() as $key => $value) {
                $logfile[] = str_pad($key . ':', 26) . ' ' . json_encode($value);
            }

            // Stack trace
            $logfile[] = $_SEP_;
            $logfile[] = '# Stack Trace:';
            $logfile[] = $exception->getTraceAsString();

            Storage::put($logdir . '/' . $UNI_GLOBAL_UNIQUE_ERROR_ID . '.log', implode("\r\n", $logfile));
        }

        return response(view('error-code', ['code' => $UNI_GLOBAL_UNIQUE_ERROR_ID, 'exception' => $exception]), $rCode);

        ////////////////////////////////////////////
        // Laravel Handler
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}

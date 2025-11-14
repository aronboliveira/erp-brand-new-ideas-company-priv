<?php

namespace App\Exceptions;

use App\Models\Utility;
use App\Config\Constants\SettingsConstants;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\{Auth, Log, Request as RequestFacade, Route};
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class Handler extends ExceptionHandler
{
    /**
     * Exception types with custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * Exception types that should not be reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Inputs never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation'
    ];

    public function register(): void
    {
        $function = __FUNCTION__;
        $class = __CLASS__;
        $this->reportable(function (Throwable $e) use ($function, $class) {
            try {
                if (str_contains($e->getMessage(), '.js.map could not be found.') || str_contains($e->getMessage(), '.well-known/appspecific/com.chrome.devtools.json')) {
                    Log::debug('min.js.map error redirected to debug channel');
                    Log::debug(
                        'min.js.map not found',
                        [
                            'exception_class' => get_class($e),
                            'message'         => $e->getMessage(),
                            'file'            => $e->getFile(),
                            'line'            => $e->getLine(),
                        ]
                    );
                    return true;
                }
                if (function_exists('request'))
                    $request = request();
                elseif (class_exists(RequestFacade::class))
                    $request = RequestFacade::instance();
                else
                    $request = app('request');
            } catch (Throwable $fetchEx) {
                Log::debug($class . '::' . $function . ' failed to fetch request', [
                    'exception' => $fetchEx::class,
                    'message' => $fetchEx->getMessage(),
                    'file' => $fetchEx->getFile(),
                    'line' => $fetchEx->getLine(),
                ]);
                $request = null;
            }
            $exceptionContext = [
                'exception_class' => get_class($e),
                'message'         => $e->getMessage(),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
                'code'            => $e->getCode(),
                'previous'        => $e->getPrevious()?->getMessage(),
            ];
            $requestContext = $request
                ? [
                    'url'           => $request->fullUrl(),
                    'method'        => $request->method(),
                    'ip'            => $request->ip(),
                    'user_agent'    => $request->userAgent(),
                    'route_name'    => optional($request->route())->getName(),
                    'action'        => optional($request->route())->getActionName(),
                    'headers'       => $request->headers->all(),
                    'query_params'  => $request->query(),
                    'payload'       => $request->except(['password', 'password_confirmation']),
                    'user'          => optional($request->user()) ? [
                        'id'    => $request->user()?->getAuthIdentifier(),
                        'email' => $request->user()?->email,
                    ] : null,
                ]
                : null;
            $mergedCtx = array_merge(
                $exceptionContext,
                ['request' => $requestContext]
            );
            if (!(str_contains($e->getMessage(), '.js.map could not be found.') || str_contains($e->getMessage(), '.well-known/appspecific/com.chrome.devtools.json')))
                Log::critical(
                    sprintf('%s::%s reportable triggered', $class, $function),
                    $mergedCtx
                );
            Log::channel(SettingsConstants::CRT_TRACE)->debug(
                sprintf('%s::%s reportable triggered', $class, $function),
                array_merge($mergedCtx, [
                    'trace' => $e->getTraceAsString()
                ])
            );
            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                ? $e->getStatusCode()
                : 500;
            if (!empty($status) && $status >= 500)
                return response('<!DOCTYPE html><html><body><h1>Server error</h1><p>' . $status . '</p></body></html>', 500);
        });
        $this->renderable(function (Throwable $e, HttpRequest $request) use ($function, $class) {
            if (!($request instanceof HttpRequest)) {
                try {
                    if (str_contains($e->getMessage(), '.js.map could not be found.')) {
                        Log::debug('min.js.map error redirected to debug channel');
                        Log::debug(
                            'min.js.map exception outside HTTP request context',
                            [
                                'exception_class' => get_class($e),
                                'message'         => $e->getMessage(),
                                'file'            => $e->getFile(),
                                'line'            => $e->getLine(),
                            ]
                        );
                        return response('', 404);
                    }
                    if (function_exists('request'))
                        $request = request();
                    elseif (class_exists(RequestFacade::class))
                        $request = RequestFacade::instance();
                    else
                        $request = app('request');
                } catch (Throwable $fetchEx) {
                    Log::debug($class . '::' . $function . ' failed to fetch request', [
                        'exception' => $fetchEx::class,
                        'message' => $fetchEx->getMessage(),
                        'file' => $fetchEx->getFile(),
                        'line' => $fetchEx->getLine(),
                    ]);
                    $request = null;
                }
            }
            $exceptionContext = [
                'exception_class' => get_class($e),
                'message'         => $e->getMessage(),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
                'code'            => $e->getCode(),
                'previous'        => $e->getPrevious()?->getMessage(),
            ];
            $requestContext = $request
                ? [
                    'url'           => $request->fullUrl(),
                    'method'        => $request->method(),
                    'ip'            => $request->ip(),
                    'user_agent'    => $request->userAgent(),
                    'route_name'    => optional($request->route())->getName(),
                    'action'        => optional($request->route())->getActionName(),
                    'headers'       => $request->headers->all(),
                    'query_params'  => $request->query(),
                    'payload'       => $request->except(['password', 'password_confirmation']),
                    'user'          => optional($request->user()) ? [
                        'id'    => $request->user()?->getAuthIdentifier(),
                        'email' => $request->user()?->email,
                    ] : null,
                ]
                : null;
            $mergedCtx = array_merge(
                $exceptionContext,
                ['request' => $requestContext]
            );
            if (!(str_contains($e->getMessage(), '.js.map could not be found.') || str_contains($e->getMessage(), '.well-known/appspecific/com.chrome.devtools.json')))
                Log::critical(
                    sprintf('%s::%s reportable triggered', $class, $function),
                    $mergedCtx
                );
            Log::channel(SettingsConstants::CRT_TRACE)->debug(
                sprintf('%s::%s reportable triggered', $class, $function),
                array_merge($mergedCtx, [
                    'trace' => $e->getTraceAsString()
                ])
            );
            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                ? $e->getStatusCode()
                : 500;
            if (
                $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                && !empty($status)
            ) {
                if ($e->getStatusCode() >= 500)
                    return response($this->getGenericServerErrorHtml($request), $status);
                else if ($e->getStatusCode() === 404)
                    return response($this->get404ErrorHtml($request), $status);
                else if ($e->getStatusCode() >= 400)
                    return response($this->getAuthErrorHtml($request), $status);
            }
            return response($this->getGenericServerErrorHtml($request), $status);
        });
    }

    public function render($request, Throwable $e): Response
    {
        try {
            return parent::render($request, $e);
        } catch (\Throwable $renderException) {
            Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed', [
                'render_exception' => $renderException::class,
                'original_exception' => $e::class,
                'message' => $renderException->getMessage(),
                'uri' => $request->getRequestUri()
            ]);
            return response($this->getGenericServerErrorHtml($request), 500);
        }
    }

    public function renderForConsole($output, Throwable $e): void
    {
        parent::renderForConsole($output, $e);
    }

    private function getGenericServerErrorHtml($request): string
    {
        $redirectUrl = $this->getRedirectUrl($request);
        $userLang = Utility::fetchUserLang(Auth::user()) ?? 'en';

        return '<!DOCTYPE html>
            <html lang="' . $userLang . '" dir="' . (in_array($userLang, ['ar', 'he']) ? 'rtl' : 'ltr') . '">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title id="page-title">Server Error</title>
                    <meta http-equiv="refresh" content="4;url=' . $redirectUrl . '">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <script src="https://unpkg.com/feather-icons"></script>
                    <style>
                        :root {
                            --primary-blue: #e8f4f8;
                            --secondary-green: #e8f6f0;
                            --accent-blue: #a7d0e4;
                            --accent-green: #a8e6cf;
                            --dark-blue: #2c5aa0;
                            --dark-green: #27ae60;
                        }
                        
                        body {
                            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-green) 100%);
                            min-height: 100vh;
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                        }

                        [dir="rtl"] {
                            text-align: right;
                        }
                        
                        .error-container {
                            background: rgba(255, 255, 255, 0.95);
                            border-radius: 20px;
                            box-shadow: 0 20px 40px rgba(44, 90, 160, 0.1);
                            backdrop-filter: blur(10px);
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            max-width: 450px;
                            margin: 0 auto;
                            padding: 3rem 2rem;
                            text-align: center;
                        }
                        
                        .spinner-container {
                            margin: 2rem 0;
                        }
                        
                        .custom-spinner {
                            width: 60px;
                            height: 60px;
                            border: 4px solid var(--accent-blue);
                            border-top: 4px solid var(--dark-blue);
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                            margin: 0 auto;
                        }
                        
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                        
                        .status-badge {
                            background: linear-gradient(135deg, var(--accent-blue), var(--accent-green));
                            color: var(--dark-blue);
                            padding: 0.5rem 1rem;
                            border-radius: 50px;
                            font-weight: 600;
                            font-size: 0.875rem;
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 1rem;
                        }

                        @keyframes pulse {
                            0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
                            40% { opacity: 1; transform: scale(1); }
                        }
                    </style>
                </head>
                <body>
                    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-4">
                        <div class="error-container">
                            <div class="status-badge">
                                <i data-feather="server" size="16"></i>
                                <span id="status-text">Server Error (500)</span>
                            </div>
                            
                            <h1 class="h3 mb-3 fw-bold" style="color: var(--dark-blue);" id="error-title">Processing Issue</h1>
                            
                            <div class="spinner-container">
                                <div class="custom-spinner"></div>
                            </div>
                            
                            <p class="text-muted mb-4" id="redirect-message">We\'re redirecting you back automatically...</p>
                            
                            <div class="d-flex justify-content-center gap-2">
                                <div style="width: 8px; height: 8px; background: var(--dark-blue); border-radius: 50%; animation: pulse 1.5s ease-in-out infinite;"></div>
                                <div style="width: 8px; height: 8px; background: var(--dark-green); border-radius: 50%; animation: pulse 1.5s ease-in-out 0.3s infinite;"></div>
                                <div style="width: 8px; height: 8px; background: var(--accent-blue); border-radius: 50%; animation: pulse 1.5s ease-in-out 0.6s infinite;"></div>
                            </div>
                        </div>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                    <script>
                            (() => { 
                                if (!window.translations) {
                        window.translations = {};
                        }
                        const t = {
                                                    en: {
                                                        title: "Server Error",
                                                        statusText: "Server Error (500)",
                                                        errorTitle: "Processing Issue",
                                                        redirectMessage: "We\'re redirecting you back automatically..."
                                                    },
                                                    ar: {
                                                        title: "خطأ في الخادم",
                                                        statusText: "خطأ في الخادم (500)",
                                                        errorTitle: "مشكلة في المعالجة",
                                                        redirectMessage: "نحن نعيد توجيهك تلقائياً..."
                                                    },
                                                    da: {
                                                        title: "Serverfejl",
                                                        statusText: "Serverfejl (500)",
                                                        errorTitle: "Behandlingsproblem",
                                                        redirectMessage: "Vi omdirigerer dig automatisk tilbage..."
                                                    },
                                                    de: {
                                                        title: "Serverfehler",
                                                        statusText: "Serverfehler (500)",
                                                        errorTitle: "Verarbeitungsproblem",
                                                        redirectMessage: "Wir leiten Sie automatisch zurück..."
                                                    },
                                                    es: {
                                                        title: "Error del Servidor",
                                                        statusText: "Error del Servidor (500)",
                                                        errorTitle: "Problema de Procesamiento",
                                                        redirectMessage: "Te estamos redirigiendo automáticamente..."
                                                    },
                                                    fr: {
                                                        title: "Erreur Serveur",
                                                        statusText: "Erreur Serveur (500)",
                                                        errorTitle: "Problème de Traitement",
                                                        redirectMessage: "Nous vous redirigeons automatiquement..."
                                                    },
                                                    he: {
                                                        title: "שגיאת שרת",
                                                        statusText: "שגיאת שרת (500)",
                                                        errorTitle: "בעיה בעיבוד",
                                                        redirectMessage: "אנחנו מפנים אותך בחזרה אוטומטית..."
                                                    },
                                                    it: {
                                                        title: "Errore del Server",
                                                        statusText: "Errore del Server (500)",
                                                        errorTitle: "Problema di Elaborazione",
                                                        redirectMessage: "Ti stiamo reindirizzando automaticamente..."
                                                    },
                                                    ja: {
                                                        title: "サーバーエラー",
                                                        statusText: "サーバーエラー (500)",
                                                        errorTitle: "処理の問題",
                                                        redirectMessage: "自動的にリダイレクトしています..."
                                                    },
                                                    nl: {
                                                        title: "Serverfout",
                                                        statusText: "Serverfout (500)",
                                                        errorTitle: "Verwerkingsprobleem",
                                                        redirectMessage: "We sturen je automatisch terug..."
                                                    },
                                                    pl: {
                                                        title: "Błąd Serwera",
                                                        statusText: "Błąd Serwera (500)",
                                                        errorTitle: "Problem z Przetwarzaniem",
                                                        redirectMessage: "Automatycznie przekierowujemy Cię z powrotem..."
                                                    },
                                                    pt: {
                                                        title: "Erro do Servidor",
                                                        statusText: "Erro do Servidor (500)",
                                                        errorTitle: "Problema de Processamento",
                                                        redirectMessage: "Estamos a redirecioná-lo automaticamente..."
                                                    },
                                                    "pt-br": {
                                                        title: "Erro do Servidor",
                                                        statusText: "Erro do Servidor (500)",
                                                        errorTitle: "Problema de Processamento",
                                                        redirectMessage: "Estamos redirecionando você automaticamente..."
                                                    },
                                                    ru: {
                                                        title: "Ошибка Сервера",
                                                        statusText: "Ошибка Сервера (500)",
                                                        errorTitle: "Проблема с Обработкой",
                                                        redirectMessage: "Мы автоматически перенаправляем вас назад..."
                                                    },
                                                    tr: {
                                                        title: "Sunucu Hatası",
                                                        statusText: "Sunucu Hatası (500)",
                                                        errorTitle: "İşleme Sorunu",
                                                        redirectMessage: "Sizi otomatik olarak geri yönlendiriyoruz..."
                                                    },
                                                    zh: {
                                                        title: "服务器错误",
                                                        statusText: "服务器错误 (500)",
                                                        errorTitle: "处理问题",
                                                        redirectMessage: "我们正在自动为您重定向..."
                                                    }
                                                };
                        Object.keys(t).forEach(
                        k =>
                            (window.translations[k] = {
                            ...(window.translations[k] || {}),
                            ...t[k],
                            })
                        );

                        const currentLang = "' . $userLang . '";
                        const translations = window.translations[currentLang] || window.translations.en;

                        function updateContent() {
                            document.getElementById("page-title").textContent = translations.title;
                            document.getElementById("status-text").textContent = translations.statusText;
                            document.getElementById("error-title").textContent = translations.errorTitle;
                            document.getElementById("redirect-message").textContent = translations.redirectMessage;
                        }

                        feather && typeof feather.replace === "function" && feather.replace();
                        updateContent();
                    
                        })();
                    </script>
                </body>
            </html>';
    }

    private function get404ErrorHtml($request): string
    {
        $redirectUrl = $this->getRedirectUrl($request);
        $redirectDelay = 8;
        $userLang = Utility::fetchUserLang(Auth::user()) ?? 'en';

        return '<!DOCTYPE html>
            <html lang="' . $userLang . '" dir="' . (in_array($userLang, ['ar', 'he']) ? 'rtl' : 'ltr') . '">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title id="page-title">Page Not Found - 404</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <script src="https://unpkg.com/feather-icons"></script>
                <style>
                :root {
                    --primary-blue: #e8f4f8;
                    --secondary-green: #e8f6f0;
                    --accent-blue: #a7d0e4;
                    --accent-green: #a8e6cf;
                    --dark-blue: #2c5aa0;
                    --dark-green: #27ae60;
                    --text-muted: #6c757d;
                    --light-gray: #f8f9fa;
                }

                body {
                    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-green) 100%);
                    min-height: 100vh;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                }

                [dir="rtl"] { text-align: right; }
                [dir="rtl"] .d-md-flex { flex-direction: row-reverse; }
                [dir="rtl"] .suggestion-item:hover { transform: translateX(-5px); }
                [dir="ltr"] .suggestion-item:hover { transform: translateX(5px); }

                .error-container {
                    position: relative;
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(44, 90, 160, 0.1);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    max-width: 650px;
                    margin: 0 auto;
                    overflow: hidden;
                }

                .error-header {
                    background: linear-gradient(135deg, var(--accent-blue), var(--accent-green));
                    padding: 2rem;
                    text-align: center;
                    color: var(--dark-blue);
                }

                .error-icon {
                    width: 100px;
                    height: 100px;
                    background: rgba(255, 255, 255, 0.9);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1rem;
                    box-shadow: 0 10px 30px rgba(44, 90, 160, 0.2);
                    position: relative;
                }

                .error-icon::before {
                    content: "404";
                    position: absolute;
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: var(--dark-blue);
                    top: -35px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: rgba(255, 255, 255, 0.9);
                    padding: 0.25rem 0.75rem;
                    border-radius: 15px;
                    border: 2px solid var(--accent-blue);
                }

                .error-content { padding: 2rem; }

                .suggestions-card {
                    background: var(--light-gray);
                    border-radius: 15px;
                    padding: 1.5rem;
                    margin: 1.5rem 0;
                    border-left: 4px solid var(--dark-green);
                }
                [dir="rtl"] .suggestions-card {
                    border-left: none;
                    border-right: 4px solid var(--dark-green);
                }

                .suggestion-item {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 0.75rem;
                    margin: 0.5rem 0;
                    background: white;
                    border-radius: 10px;
                    transition: all 0.3s ease;
                    text-decoration: none;
                    color: var(--text-muted);
                    border: 1px solid transparent;
                }

                .suggestion-item:hover {
                    background: var(--primary-blue);
                    border-color: var(--accent-blue);
                    color: var(--dark-blue);
                }

                .countdown-display {
                    background: var(--secondary-green);
                    border-radius: 15px;
                    padding: 1.5rem;
                    margin: 1.5rem 0;
                    text-align: center;
                    border-left: 4px solid var(--dark-blue);
                }
                [dir="rtl"] .countdown-display {
                    border-left: none;
                    border-right: 4px solid var(--dark-blue);
                }

                .countdown-number {
                    font-size: 2rem;
                    font-weight: 700;
                    color: var(--dark-blue);
                    margin-bottom: 0.5rem;
                }

                .progress {
                    height: 6px;
                    background: var(--primary-blue);
                    border-radius: 10px;
                    overflow: visible;
                    margin: 1rem 0;
                }

                .progress-bar {
                    background: linear-gradient(90deg, var(--dark-blue), var(--dark-green));
                    border-radius: 10px;
                    transition: width 0.3s ease;
                }
                [dir="rtl"] .progress-bar {
                    background: linear-gradient(270deg, var(--dark-blue), var(--dark-green));
                }

                .btn-custom {
                    background: linear-gradient(135deg, var(--dark-blue), var(--dark-green));
                    border: none;
                    border-radius: 50px;
                    padding: 12px 24px;
                    color: white;
                    text-decoration: none;
                    transition: all 0.3s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    box-shadow: 0 5px 15px rgba(44, 90, 160, 0.3);
                }
                .btn-custom:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(44, 90, 160, 0.4);
                    color: white;
                }

                .btn-outline-custom {
                    background: transparent;
                    border: 2px solid var(--accent-blue);
                    color: var(--dark-blue);
                    border-radius: 50px;
                    padding: 12px 24px;
                    text-decoration: none;
                    transition: all 0.3s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }
                .btn-outline-custom:hover {
                    background: var(--accent-blue);
                    color: var(--dark-blue);
                    transform: translateY(-2px);
                }

                .floating-elements {
                    position: absolute;
                    inset: 0;
                    overflow: hidden;
                    pointer-events: none;
                }
                .floating-element {
                    position: absolute;
                    opacity: 0.1;
                    animation: float 6s ease-in-out infinite;
                }
                .floating-element:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; }
                .floating-element:nth-child(2) { top: 60%; right: 15%; animation-delay: 2s; }
                .floating-element:nth-child(3) { bottom: 30%; left: 20%; animation-delay: 4s; }

                [dir="rtl"] .floating-element:nth-child(1) { left: auto; right: 10%; }
                [dir="rtl"] .floating-element:nth-child(2) { right: auto; left: 15%; }
                [dir="rtl"] .floating-element:nth-child(3) { left: auto; right: 20%; }

                @keyframes float {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-10px); }
                }
                </style>
            </head>
            <body>
                <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-4">
                <div class="error-container">
                    <div class="floating-elements">
                    <div class="floating-element"><i data-feather="circle"></i></div>
                    <div class="floating-element"><i data-feather="triangle"></i></div>
                    <div class="floating-element"><i data-feather="square"></i></div>
                    </div>

                    <div class="error-header">
                    <div class="error-icon">
                        <i data-feather="alert-triangle" size="40" color="#2c5aa0"></i>
                    </div>
                    <h1 class="h2 mb-2 fw-bold" id="error-title">Server Error</h1>
                    <p class="mb-0" id="error-subtitle">Something went wrong on our end</p>
                    </div>

                    <div class="error-content">
                    <img src="/assets/images/404-art.webp" alt="404 Error" loading="lazy" decoding="async" />
                    <div class="text-center mb-4">
                        <p class="text-muted" id="error-description">
                        We encountered a technical issue while processing your request. Don\'t worry, we\'re working to fix it!
                        </p>
                    </div>

                    <div class="countdown-display">
                        <div class="countdown-number" id="countdown">' . $redirectDelay . '</div>
                        <small class="text-muted" id="redirect-text">Automatically redirecting in seconds</small>
                        <div class="progress mt-3">
                        <div class="progress-bar" role="progressbar" id="progressFill" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                        <a href="' . $redirectUrl . '" class="btn btn-custom">
                        <i data-feather="arrow-left" size="18"></i>
                        <span id="go-back-btn">Go Back Now</span>
                        </a>
                        <a href="' . url('/') . '" class="btn btn-outline-custom">
                        <i data-feather="home" size="18"></i>
                        <span id="home-btn">Home Page</span>
                        </a>
                    </div>

                    <div class="mt-3 text-center text-muted" id="cancel-hint-wrap">
                        <i data-feather="info" size="16"></i>
                        <span id="cancel-hint">Press <kbd>ESC</kbd> to cancel automatic redirect</span>
                    </div>
                    </div>
                </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <script>
                (() => {
                    if (!window.translations) { window.translations = {}; }
                    const t = {
                    en: {
                        title: "Server Error",
                        subtitle: "Something went wrong on our end",
                        description: "We encountered a technical issue while processing your request. Don\'t worry, we\'re working to fix it!",
                        redirectText: "Automatically redirecting in seconds",
                        redirectTextCancelled: "Automatic redirect cancelled",
                        goBackBtn: "Go Back Now",
                        homeBtn: "Home Page",
                        cancelHint: "Press <kbd>ESC</kbd> to cancel automatic redirect"
                    },
                    ar: {
                        title: "خطأ في الخادم",
                        subtitle: "حدث خطأ من جانبنا",
                        description: "واجهنا مشكلة تقنية أثناء معالجة طلبك. لا تقلق، نحن نعمل على إصلاحها!",
                        redirectText: "إعادة توجيه تلقائية خلال ثواني",
                        redirectTextCancelled: "تم إلغاء إعادة التوجيه التلقائية",
                        goBackBtn: "العودة الآن",
                        homeBtn: "الصفحة الرئيسية",
                        cancelHint: "اضغط <kbd>ESC</kbd> لإلغاء إعادة التوجيه التلقائية"
                    },
                    da: {
                        title: "Serverfejl",
                        subtitle: "Noget gik galt på vores side",
                        description: "Vi stødte på et teknisk problem under behandlingen af din anmodning. Bare rolig, vi arbejder på at løse det!",
                        redirectText: "Omdirigerer automatisk om sekunder",
                        redirectTextCancelled: "Automatisk omdirigering annulleret",
                        goBackBtn: "Gå Tilbage Nu",
                        homeBtn: "Forside",
                        cancelHint: "Tryk <kbd>ESC</kbd> for at annullere automatisk omdirigering"
                    },
                    de: {
                        title: "Serverfehler",
                        subtitle: "Etwas ist auf unserer Seite schief gelaufen",
                        description: "Wir sind auf ein technisches Problem bei der Bearbeitung Ihrer Anfrage gestoßen. Keine Sorge, wir arbeiten daran!",
                        redirectText: "Automatische Weiterleitung in Sekunden",
                        redirectTextCancelled: "Automatische Weiterleitung abgebrochen",
                        goBackBtn: "Jetzt Zurück",
                        homeBtn: "Startseite",
                        cancelHint: "Drücken Sie <kbd>ESC</kbd>, um die automatische Weiterleitung abzubrechen"
                    },
                    es: {
                        title: "Error del Servidor",
                        subtitle: "Algo salió mal de nuestro lado",
                        description: "Encontramos un problema técnico al procesar su solicitud. ¡No se preocupe, estamos trabajando para solucionarlo!",
                        redirectText: "Redirigiendo automáticamente en segundos",
                        redirectTextCancelled: "Redirección automática cancelada",
                        goBackBtn: "Volver Ahora",
                        homeBtn: "Página de Inicio",
                        cancelHint: "Presione <kbd>ESC</kbd> para cancelar la redirección automática"
                    },
                    fr: {
                        title: "Erreur Serveur",
                        subtitle: "Quelque chose s\'est mal passé de notre côté",
                        description: "Nous avons rencontré un problème technique lors du traitement de votre demande. Ne vous inquiétez pas, nous travaillons pour le résoudre !",
                        redirectText: "Redirection automatique dans quelques secondes",
                        redirectTextCancelled: "Redirection automatique annulée",
                        goBackBtn: "Retour Maintenant",
                        homeBtn: "Page d\'Accueil",
                        cancelHint: "Appuyez sur <kbd>ESC</kbd> pour annuler la redirection automatique"
                    },
                    he: {
                        title: "שגיאת שרת",
                        subtitle: "משהו השתבש מהצד שלנו",
                        description: "נתקלנו בבעיה טכנית במהלך עיבוד הבקשה שלך. אל תדאג, אנחנו עובדים על תיקון זה!",
                        redirectText: "מפנה אוטומטית תוך שניות",
                        redirectTextCancelled: "הפניה אוטומטית בוטלה",
                        goBackBtn: "חזור עכשיו",
                        homeBtn: "עמוד הבית",
                        cancelHint: "לחץ <kbd>ESC</kbd> כדי לבטל הפניה אוטומטית"
                    },
                    it: {
                        title: "Errore del Server",
                        subtitle: "Qualcosa è andato storto dal nostro lato",
                        description: "Abbiamo riscontrato un problema tecnico durante l\'elaborazione della tua richiesta. Non preoccuparti, stiamo lavorando per risolverlo!",
                        redirectText: "Reindirizzamento automatico in pochi secondi",
                        redirectTextCancelled: "Reindirizzamento automatico annullato",
                        goBackBtn: "Torna Ora",
                        homeBtn: "Pagina Iniziale",
                        cancelHint: "Premi <kbd>ESC</kbd> per annullare il reindirizzamento automatico"
                    },
                    ja: {
                        title: "サーバーエラー",
                        subtitle: "当方で何か問題が発生しました",
                        description: "リクエストの処理中に技術的な問題が発生しました。ご心配なく、修正に取り組んでいます！",
                        redirectText: "数秒後に自動的にリダイレクトします",
                        redirectTextCancelled: "自動リダイレクトがキャンセルされました",
                        goBackBtn: "今すぐ戻る",
                        homeBtn: "ホームページ",
                        cancelHint: "<kbd>ESC</kbd>を押して自動リダイレクトをキャンセル"
                    },
                    nl: {
                        title: "Serverfout",
                        subtitle: "Er is iets misgegaan aan onze kant",
                        description: "We ondervonden een technisch probleem bij het verwerken van uw verzoek. Maak je geen zorgen, we werken eraan om het op te lossen!",
                        redirectText: "Automatisch doorverwijzen over enkele seconden",
                        redirectTextCancelled: "Automatische doorverwijzing geannuleerd",
                        goBackBtn: "Ga Nu Terug",
                        homeBtn: "Startpagina",
                        cancelHint: "Druk op <kbd>ESC</kbd> om automatische doorverwijzing te annuleren"
                    },
                    pl: {
                        title: "Błąd Serwera",
                        subtitle: "Coś poszło nie tak po naszej stronie",
                        description: "Napotkaliśmy problem techniczny podczas przetwarzania Twojego żądania. Nie martw się, pracujemy nad jego naprawą!",
                        redirectText: "Automatyczne przekierowanie za kilka sekund",
                        redirectTextCancelled: "Automatyczne przekierowanie anulowane",
                        goBackBtn: "Wróć Teraz",
                        homeBtn: "Strona Główna",
                        cancelHint: "Naciśnij <kbd>ESC</kbd> aby anulować automatyczne przekierowanie"
                    },
                    pt: {
                        title: "Erro do Servidor",
                        subtitle: "Algo correu mal do nosso lado",
                        description: "Encontrámos um problema técnico ao processar o seu pedido. Não se preocupe, estamos a trabalhar para o resolver!",
                        redirectText: "Redirecionamento automático em segundos",
                        redirectTextCancelled: "Redirecionamento automático cancelado",
                        goBackBtn: "Voltar Agora",
                        homeBtn: "Página Inicial",
                        cancelHint: "Pressione <kbd>ESC</kbd> para cancelar o redirecionamento automático"
                    },
                    "pt-br": {
                        title: "Erro do Servidor",
                        subtitle: "Algo deu errado do nosso lado",
                        description: "Encontramos um problema técnico ao processar sua solicitação. Não se preocupe, estamos trabalhando para corrigi-lo!",
                        redirectText: "Redirecionamento automático em segundos",
                        redirectTextCancelled: "Redirecionamento automático cancelado",
                        goBackBtn: "Voltar Agora",
                        homeBtn: "Página Inicial",
                        cancelHint: "Pressione <kbd>ESC</kbd> para cancelar o redirecionamento automático"
                    },
                    ru: {
                        title: "Ошибка Сервера",
                        subtitle: "Что-то пошло не так с нашей стороны",
                        description: "Мы столкнулись с технической проблемой при обработке вашего запроса. Не волнуйтесь, мы работаем над её устранением!",
                        redirectText: "Автоматическое перенаправление через несколько секунд",
                        redirectTextCancelled: "Автоматическое перенаправление отменено",
                        goBackBtn: "Вернуться Сейчас",
                        homeBtn: "Главная Страница",
                        cancelHint: "Нажмите <kbd>ESC</kbd> для отмены автоматического перенаправления"
                    },
                    tr: {
                        title: "Sunucu Hatası",
                        subtitle: "Bizim tarafımızda bir şeyler ters gitti",
                        description: "İsteğinizi işlerken teknik bir sorunla karşılaştık. Endişelenmeyin, düzeltmek için çalışıyoruz!",
                        redirectText: "Saniyeler içinde otomatik yönlendirme",
                        redirectTextCancelled: "Otomatik yönlendirme iptal edildi",
                        goBackBtn: "Şimdi Geri Dön",
                        homeBtn: "Ana Sayfa",
                        cancelHint: "Otomatik yönlendirmeyi iptal etmek için <kbd>ESC</kbd> tuşuna basın"
                    },
                    zh: {
                        title: "服务器错误",
                        subtitle: "我们这边出了点问题",
                        description: "在处理您的请求时遇到了技术问题。别担心，我们正在努力修复！",
                        redirectText: "几秒钟后自动重定向",
                        redirectTextCancelled: "自动重定向已取消",
                        goBackBtn: "立即返回",
                        homeBtn: "首页",
                        cancelHint: "按<kbd>ESC</kbd>取消自动重定向"
                    }
                    };
                    Object.keys(t).forEach(k => {
                    window.translations[k] = { ...(window.translations[k] || {}), ...t[k] };
                    });

                    const currentLang = "' . $userLang . '";
                    const translations = window.translations[currentLang] || window.translations.en;

                    function updateContent() {
                    document.getElementById("page-title").textContent = translations.title;
                    document.getElementById("error-title").textContent = translations.title;
                    document.getElementById("error-subtitle").textContent = translations.subtitle;
                    document.getElementById("error-description").textContent = translations.description;
                    document.getElementById("redirect-text").textContent = translations.redirectText;
                    document.getElementById("go-back-btn").textContent = translations.goBackBtn;
                    document.getElementById("home-btn").textContent = translations.homeBtn;
                    document.getElementById("cancel-hint").innerHTML = translations.cancelHint;
                    }

                    feather && typeof feather.replace === "function" && feather.replace();
                    updateContent();

                    let timeLeft = ' . $redirectDelay . ';
                    const countdownEl = document.getElementById("countdown");
                    const progressEl = document.getElementById("progressFill");
                    const totalTime = timeLeft;

                    const timer = setInterval(() => {
                    timeLeft--;
                    countdownEl.textContent = timeLeft;

                    const progress = ((totalTime - timeLeft) / totalTime) * 100;
                    progressEl.style.width = progress + "%";

                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        window.location.href = "' . $redirectUrl . '";
                    }
                    }, 1000);

                    document.addEventListener("keydown", function(e) {
                    if (e.key === "Escape") {
                        clearInterval(timer);
                        countdownEl.textContent = "∞";
                        document.getElementById("redirect-text").textContent = translations.redirectTextCancelled;
                        progressEl.style.width = "0%";
                    }
                    });
                })();
                </script>
            </body>
            </html>';
    }

    private function getAuthErrorHtml($request): string
    {
        $redirectUrl = $this->getRedirectUrl($request, '/login');
        $redirectDelay = 5;
        $userLang = Utility::fetchUserLang(Auth::user()) ?? 'en';

        return '<!DOCTYPE html>
                                                <html lang="' . $userLang . '" dir="' . (in_array($userLang, ['ar', 'he']) ? 'rtl' : 'ltr') . '">
                                                    <head>
                                                        <meta charset="UTF-8">
                                                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                                        <title id="page-title">Access Denied</title>
                                                        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                                                        <script src="https://unpkg.com/feather-icons"></script>
                                                        <style>
                                                            :root {
                                                                --primary-blue: #e8f4f8;
                                                                --secondary-green: #e8f6f0;
                                                                --accent-blue: #a7d0e4;
                                                                --accent-green: #a8e6cf;
                                                                --dark-blue: #2c5aa0;
                                                                --dark-green: #27ae60;
                                                                --warning-orange: #f39c12;
                                                                --text-muted: #6c757d;
                                                            }
                                                            
                                                            body {
                                                                background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-green) 100%);
                                                                min-height: 100vh;
                                                                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                                                            }
                                    
                                                            [dir="rtl"] {
                                                                text-align: right;
                                                            }
                                    
                                                            [dir="rtl"] .d-md-flex {
                                                                flex-direction: row-reverse;
                                                            }
                                    
                                                            [dir="rtl"] .cancel-hint {
                                                                text-align: right;
                                                            }
                                                            
                                                            .error-container {
                                                                background: rgba(255, 255, 255, 0.95);
                                                                border-radius: 20px;
                                                                box-shadow: 0 20px 40px rgba(44, 90, 160, 0.1);
                                                                backdrop-filter: blur(10px);
                                                                border: 1px solid rgba(255, 255, 255, 0.3);
                                                                max-width: 550px;
                                                                margin: 0 auto;
                                                                overflow: hidden;
                                                            }
                                                            
                                                            .error-header {
                                                                background: linear-gradient(135deg, var(--accent-blue), var(--accent-green));
                                                                padding: 2rem;
                                                                text-align: center;
                                                                color: var(--dark-blue);
                                                            }
                                                            
                                                            .error-icon {
                                                                width: 80px;
                                                                height: 80px;
                                                                background: rgba(255, 255, 255, 0.9);
                                                                border-radius: 50%;
                                                                display: flex;
                                                                align-items: center;
                                                                justify-content: center;
                                                                margin: 0 auto 1rem;
                                                                box-shadow: 0 10px 30px rgba(243, 156, 18, 0.2);
                                                            }
                                                            
                                                            .error-content {
                                                                padding: 2rem;
                                                            }
                                                            
                                                            .countdown-display {
                                                                background: rgba(243, 156, 18, 0.08);
                                                                border-radius: 15px;
                                                                padding: 1.5rem;
                                                                margin: 1.5rem 0;
                                                                text-align: center;
                                                                border-left: 4px solid var(--warning-orange);
                                                            }
                                    
                                                            [dir="rtl"] .countdown-display {
                                                                border-left: none;
                                                                border-right: 4px solid var(--warning-orange);
                                                            }
                                                            
                                                            .countdown-number {
                                                                font-size: 2.5rem;
                                                                font-weight: 700;
                                                                color: var(--warning-orange);
                                                                margin-bottom: 0.5rem;
                                                            }
                                                            
                                                            .progress {
                                                                height: 8px;
                                                                background: rgba(243, 156, 18, 0.15);
                                                                border-radius: 10px;
                                                                overflow: visible;
                                                                margin: 1.5rem 0;
                                                            }
                                                            
                                                            .progress-bar {
                                                                background: linear-gradient(90deg, var(--warning-orange), #e67e22);
                                                                border-radius: 10px;
                                                                position: relative;
                                                                transition: width 0.3s ease;
                                                            }
                                    
                                                            [dir="rtl"] .progress-bar {
                                                                background: linear-gradient(270deg, var(--warning-orange), #e67e22);
                                                            }
                                                            
                                                            .progress-bar::after {
                                                                content: "";
                                                                position: absolute;
                                                                top: -2px;
                                                                right: -2px;
                                                                width: 12px;
                                                                height: 12px;
                                                                background: var(--warning-orange);
                                                                border-radius: 50%;
                                                                border: 2px solid white;
                                                                box-shadow: 0 2px 10px rgba(243, 156, 18, 0.3);
                                                            }
                                    
                                                            [dir="rtl"] .progress-bar::after {
                                                                right: auto;
                                                                left: -2px;
                                                            }
                                                            
                                                            .btn-custom {
                                                                background: linear-gradient(135deg, var(--dark-blue), var(--dark-green));
                                                                border: none;
                                                                border-radius: 50px;
                                                                padding: 12px 24px;
                                                                color: white;
                                                                text-decoration: none;
                                                                transition: all 0.3s ease;
                                                                display: inline-flex;
                                                                align-items: center;
                                                                gap: 8px;
                                                                box-shadow: 0 5px 15px rgba(44, 90, 160, 0.3);
                                                            }
                                                            
                                                            .btn-custom:hover {
                                                                transform: translateY(-2px);
                                                                box-shadow: 0 8px 25px rgba(44, 90, 160, 0.4);
                                                                color: white;
                                                            }
                                                            
                                                            .btn-outline-custom {
                                                                background: transparent;
                                                                border: 2px solid var(--accent-blue);
                                                                color: var(--dark-blue);
                                                                border-radius: 50px;
                                                                padding: 12px 24px;
                                                                text-decoration: none;
                                                                transition: all 0.3s ease;
                                                                display: inline-flex;
                                                                align-items: center;
                                                                gap: 8px;
                                                            }
                                                            
                                                            .btn-outline-custom:hover {
                                                                background: var(--accent-blue);
                                                                color: var(--dark-blue);
                                                                transform: translateY(-2px);
                                                            }
                                                            
                                                            .cancel-hint {
                                                                background: var(--secondary-green);
                                                                border-radius: 10px;
                                                                padding: 0.75rem 1rem;
                                                                font-size: 0.875rem;
                                                                color: var(--text-muted);
                                                                margin-top: 1rem;
                                                                display: flex;
                                                                align-items: center;
                                                                gap: 8px;
                                                            }
                                                        </style>
                                                    </head>
                                                    <body>
                                                        <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-4">
                                                            <div class="error-container">
                                                                <div class="error-header">
                                                                    <div class="error-icon">
                                                                        <i data-feather="lock" size="40" color="#f39c12"></i>
                                                                    </div>
                                                                    <h1 class="h2 mb-2 fw-bold" id="error-title">Access Denied</h1>
                                                                    <p class="mb-0" id="error-subtitle">Authentication required</p>
                                                                </div>
                                                                
                                                                <div class="error-content">
                                                                    <div class="text-center mb-4">
                                                                        <p class="text-muted" id="error-description">You don\'t have permission to access this resource. Please log in to continue.</p>
                                                                    </div>
                                                                    
                                                                    <div class="countdown-display">
                                                                        <div class="countdown-number" id="countdown">' . $redirectDelay . '</div>
                                                                        <small class="text-muted" id="redirect-text">Redirecting to login page</small>
                                                                        <div class="progress">
                                                                            <div class="progress-bar" id="progressFill" style="width: 0%"></div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                                                                        <a href="' . $redirectUrl . '" class="btn btn-custom">
                                                                            <i data-feather="log-in" size="18"></i>
                                                                            <span id="login-btn">Login Now</span>
                                                                        </a>
                                                                        <a href="' . url('/') . '" class="btn btn-outline-custom">
                                                                            <i data-feather="home" size="18"></i>
                                                                            <span id="home-btn">Home Page</span>
                                                                        </a>
                                                                    </div>
                                                                    
                                                                    <div class="cancel-hint">
                                                                        <i data-feather="info" size="16"></i>
                                                                        <span id="cancel-hint">Press <kbd>ESC</kbd> to cancel automatic redirect</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                    
                                                        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                                                            <script>
                                        (() => { 
                                            if (!window.translations) {
                                window.translations = {};
                                }
                                const t = {
                                                                en: {
                                                                    title: "Access Denied",
                                                                    subtitle: "Authentication required",
                                                                    description: "You don\'t have permission to access this resource. Please log in to continue.",
                                                                    redirectText: "Redirecting to login page",
                                                                    redirectTextCancelled: "Automatic redirect cancelled",
                                                                    loginBtn: "Login Now",
                                                                    homeBtn: "Home Page",
                                                                    cancelHint: "Press <kbd>ESC</kbd> to cancel automatic redirect"
                                                                },
                                                                ar: {
                                                                    title: "تم رفض الوصول",
                                                                    subtitle: "مطلوب المصادقة",
                                                                    description: "ليس لديك إذن للوصول إلى هذا المورد. يرجى تسجيل الدخول للمتابعة.",
                                                                    redirectText: "إعادة توجيه إلى صفحة تسجيل الدخول",
                                                                    redirectTextCancelled: "تم إلغاء إعادة التوجيه التلقائية",
                                                                    loginBtn: "تسجيل الدخول الآن",
                                                                    homeBtn: "الصفحة الرئيسية",
                                                                    cancelHint: "اضغط <kbd>ESC</kbd> لإلغاء إعادة التوجيه التلقائية"
                                                                },
                                                                da: {
                                                                    title: "Adgang Nægtet",
                                                                    subtitle: "Godkendelse påkrævet",
                                                                    description: "Du har ikke tilladelse til at få adgang til denne ressource. Log venligst ind for at fortsætte.",
                                                                    redirectText: "Omdirigerer til login side",
                                                                    redirectTextCancelled: "Automatisk omdirigering annulleret",
                                                                    loginBtn: "Log Ind Nu",
                                                                    homeBtn: "Forside",
                                                                    cancelHint: "Tryk <kbd>ESC</kbd> for at annullere automatisk omdirigering"
                                                                },
                                                                de: {
                                                                    title: "Zugriff Verweigert",
                                                                    subtitle: "Authentifizierung erforderlich",
                                                                    description: "Sie haben keine Berechtigung auf diese Ressource zuzugreifen. Bitte melden Sie sich an, um fortzufahren.",
                                                                    redirectText: "Weiterleitung zur Anmeldeseite",
                                                                    redirectTextCancelled: "Automatische Weiterleitung abgebrochen",
                                                                    loginBtn: "Jetzt Anmelden",
                                                                    homeBtn: "Startseite",
                                                                    cancelHint: "Drücken Sie <kbd>ESC</kbd>, um die automatische Weiterleitung abzubrechen"
                                                                },
                                                                es: {
                                                                    title: "Acceso Denegado",
                                                                    subtitle: "Autenticación requerida",
                                                                    description: "No tienes permiso para acceder a este recurso. Por favor, inicia sesión para continuar.",
                                                                    redirectText: "Redirigiendo a la página de inicio de sesión",
                                                                    redirectTextCancelled: "Redirección automática cancelada",
                                                                    loginBtn: "Iniciar Sesión Ahora",
                                                                    homeBtn: "Página de Inicio",
                                                                    cancelHint: "Presione <kbd>ESC</kbd> para cancelar la redirección automática"
                                                                },
                                                                fr: {
                                                                    title: "Accès Refusé",
                                                                    subtitle: "Authentification requise",
                                                                    description: "Vous n\'avez pas l\'autorisation d\'accéder à cette ressource. Veuillez vous connecter pour continuer.",
                                                                    redirectText: "Redirection vers la page de connexion",
                                                                    redirectTextCancelled: "Redirection automatique annulée",
                                                                    loginBtn: "Se Connecter Maintenant",
                                                                    homeBtn: "Page d\'Accueil",
                                                                    cancelHint: "Appuyez sur <kbd>ESC</kbd> pour annuler la redirection automatique"
                                                                },
                                                                he: {
                                                                    title: "הגישה נדחתה",
                                                                    subtitle: "נדרש אימות",
                                                                    description: "אין לך הרשאה לגשת למשאב זה. אנא התחבר כדי להמשיך.",
                                                                    redirectText: "מפנה לעמוד ההתחברות",
                                                                    redirectTextCancelled: "הפניה אוטומטית בוטלה",
                                                                    loginBtn: "התחבר עכשיו",
                                                                    homeBtn: "עמוד הבית",
                                                                    cancelHint: "לחץ <kbd>ESC</kbd> כדי לבטל הפניה אוטומטית"
                                                                },
                                                                it: {
                                                                    title: "Accesso Negato",
                                                                    subtitle: "Autenticazione richiesta",
                                                                    description: "Non hai il permesso di accedere a questa risorsa. Per favore accedi per continuare.",
                                                                    redirectText: "Reindirizzamento alla pagina di accesso",
                                                                    redirectTextCancelled: "Reindirizzamento automatico annullato",
                                                                    loginBtn: "Accedi Ora",
                                                                    homeBtn: "Pagina Iniziale",
                                                                    cancelHint: "Premi <kbd>ESC</kbd> per annullare il reindirizzamento automatico"
                                                                },
                                                                ja: {
                                                                    title: "アクセス拒否",
                                                                    subtitle: "認証が必要です",
                                                                    description: "このリソースにアクセスする権限がありません。続行するにはログインしてください。",
                                                                    redirectText: "ログインページにリダイレクト中",
                                                                    redirectTextCancelled: "自動リダイレクトがキャンセルされました",
                                                                    loginBtn: "今すぐログイン",
                                                                    homeBtn: "ホームページ",
                                                                    cancelHint: "<kbd>ESC</kbd>を押して自動リダイレクトをキャンセル"
                                                                },
                                                                nl: {
                                                                    title: "Toegang Geweigerd",
                                                                    subtitle: "Authenticatie vereist",
                                                                    description: "Je hebt geen toestemming om toegang te krijgen tot deze resource. Log in om door te gaan.",
                                                                    redirectText: "Doorverwijzen naar inlogpagina",
                                                                    redirectTextCancelled: "Automatische doorverwijzing geannuleerd",
                                                                    loginBtn: "Nu Inloggen",
                                                                    homeBtn: "Startpagina",
                                                                    cancelHint: "Druk op <kbd>ESC</kbd> om automatische doorverwijzing te annuleren"
                                                                },
                                                                pl: {
                                                                    title: "Dostęp Zabroniony",
                                                                    subtitle: "Wymagana autoryzacja",
                                                                    description: "Nie masz uprawnień do dostępu do tego zasobu. Zaloguj się, aby kontynuować.",
                                                                    redirectText: "Przekierowanie do strony logowania",
                                                                    redirectTextCancelled: "Automatyczne przekierowanie anulowane",
                                                                    loginBtn: "Zaloguj się Teraz",
                                                                    homeBtn: "Strona Główna",
                                                                    cancelHint: "Naciśnij <kbd>ESC</kbd> aby anulować automatyczne przekierowanie"
                                                                },
                                                                pt: {
                                                                    title: "Acesso Negado",
                                                                    subtitle: "Autenticação necessária",
                                                                    description: "Não tem permissão para aceder a este recurso. Por favor, inicie sessão para continuar.",
                                                                    redirectText: "Redirecionando para a página de login",
                                                                    redirectTextCancelled: "Redirecionamento automático cancelado",
                                                                    loginBtn: "Fazer Login Agora",
                                                                    homeBtn: "Página Inicial",
                                                                    cancelHint: "Pressione <kbd>ESC</kbd> para cancelar o redirecionamento automático"
                                                                },
                                                                "pt-br": {
                                                                    title: "Acesso Negado",
                                                                    subtitle: "Autenticação necessária",
                                                                    description: "Você não tem permissão para acessar este recurso. Por favor, faça login para continuar.",
                                                                    redirectText: "Redirecionando para a página de login",
                                                                    redirectTextCancelled: "Redirecionamento automático cancelado",
                                                                    loginBtn: "Fazer Login Agora",
                                                                    homeBtn: "Página Inicial",
                                                                    cancelHint: "Pressione <kbd>ESC</kbd> para cancelar o redirecionamento automático"
                                                                },
                                                                ru: {
                                                                    title: "Доступ Запрещён",
                                                                    subtitle: "Требуется аутентификация",
                                                                    description: "У вас нет разрешения на доступ к этому ресурсу. Пожалуйста, войдите в систему, чтобы продолжить.",
                                                                    redirectText: "Перенаправление на страницу входа",
                                                                    redirectTextCancelled: "Автоматическое перенаправление отменено",
                                                                    loginBtn: "Войти Сейчас",
                                                                    homeBtn: "Главная Страница",
                                                                    cancelHint: "Нажмите <kbd>ESC</kbd> для отмены автоматического перенаправления"
                                                                },
                                                                tr: {
                                                                    title: "Erişim Reddedildi",
                                                                    subtitle: "Kimlik doğrulama gerekli",
                                                                    description: "Bu kaynağa erişim izniniz yok. Devam etmek için lütfen giriş yapın.",
                                                                    redirectText: "Giriş sayfasına yönlendiriliyor",
                                                                    redirectTextCancelled: "Otomatik yönlendirme iptal edildi",
                                                                    loginBtn: "Şimdi Giriş Yap",
                                                                    homeBtn: "Ana Sayfa",
                                                                    cancelHint: "Otomatik yönlendirmeyi iptal etmek için <kbd>ESC</kbd> tuşuna basın"
                                                                },
                                                                zh: {
                                                                    title: "访问被拒绝",
                                                                    subtitle: "需要身份验证",
                                                                    description: "您没有访问此资源的权限。请登录以继续。",
                                                                    redirectText: "重定向到登录页面",
                                                                    redirectTextCancelled: "自动重定向已取消",
                                                                    loginBtn: "立即登录",
                                                                    homeBtn: "首页",
                                                                    cancelHint: "按<kbd>ESC</kbd>取消自动重定向"
                                                                }
                                                            };
                                Object.keys(t).forEach(
                                k =>
                                    (window.translations[k] = {
                                    ...(window.translations[k] || {}),
                                    ...t[k],
                                    })
                                );
                        
                                                const currentLang = "' . $userLang . '";
                                                const translations = window.translations[currentLang] || window.translations.en;
                        
                                                function updateContent() {
                                                    document.getElementById("page-title").textContent = translations.title;
                                                    document.getElementById("error-title").textContent = translations.title;
                                                    document.getElementById("error-subtitle").textContent = translations.subtitle;
                                                    document.getElementById("error-description").textContent = translations.description;
                                                    document.getElementById("redirect-text").textContent = translations.redirectText;
                                                    document.getElementById("login-btn").textContent = translations.loginBtn;
                                                    document.getElementById("home-btn").textContent = translations.homeBtn;
                                                    document.getElementById("cancel-hint").innerHTML = translations.cancelHint;
                                                }
                        
                                                feather && typeof feather.replace === "function" && feather.replace();
                                                updateContent();
                                                
                                                let timeLeft = ' . $redirectDelay . ';
                                                const countdownEl = document.getElementById("countdown");
                                                const progressEl = document.getElementById("progressFill");
                                                const totalTime = timeLeft;
                                                
                                                const timer = setInterval(() => {
                                                    timeLeft--;
                                                    countdownEl.textContent = timeLeft;
                                                    
                                                    const progress = ((totalTime - timeLeft) / totalTime) * 100;
                                                    progressEl.style.width = progress + "%";
                                                    
                                                    if (timeLeft <= 0) {
                                                        clearInterval(timer);
                                                        window.location.href = "' . $redirectUrl . '";
                                                    }
                                                }, 1000);
                                                
                                                document.addEventListener("keydown", function(e) {
                                                    if (e.key === "Escape") {
                                                        clearInterval(timer);
                                                        countdownEl.textContent = "∞";
                                                        document.getElementById("redirect-text").textContent = translations.redirectTextCancelled;
                                                        progressEl.style.width = "0%";
                                                    }
                                                });
                                            
                            })();
                        </script>
                    </body>
                </html>';
    }

    private function getRedirectUrl($request, $fallback = null): string
    {
        $sessionId = session()->getId();
        $lastFunctionalKey = 'last_route_' . $sessionId;
        $lastFunctional = \Illuminate\Support\Facades\Cache::get($lastFunctionalKey);
        if ($lastFunctional && isset($lastFunctional['name'])) {
            try {
                return route($lastFunctional['name']);
            } catch (\Exception $e) {
                // Route doesn't exist anymore
            }
        }
        if ($fallback)
            return url($fallback);
        $safeRoutes = ['home', 'dashboard', 'login'];
        foreach ($safeRoutes as $route)
            if (Route::has($route))
                return route($route);
        return url('/');
    }
}

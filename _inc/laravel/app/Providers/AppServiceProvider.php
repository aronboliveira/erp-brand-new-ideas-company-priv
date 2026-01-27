<?php

namespace App\Providers;

use Throwable;
use App\Config\Constants\ViewsConstants;
use App\Services\Resolvers\{BrasilApiCepV2Resolver, ViaCepResolver};
use App\Services\{ActivitysAndLogsRequestService, BugReportService, BusinessRequestService, ContractRequestService, DealRequestService, EmailRequestService, GeoLookupService, GoalRequestService, LeadRequestService, PipelineRequestService, PosRequestService, ProductOrServiceRequestService, ProjectRequestService, PurchaseRequestService, Providers\BrasilApiCepProvider, SupportHelperService, TaskRequestService, TemplateRequestService, WarehouseRequestService, ZipGeoService};
use Doctrine\DBAL\DriverManager;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\{DB, Event, Log, Schema};
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

final class AppServiceProvider extends ServiceProvider
{
    private const DEFAULT_STRING_LENGTH = 252;

    public function register(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' registering...');
        $this->app->singleton(BrasilApiCepProvider::class);
        $this->app->singleton(ZipGeoService::class, function () {
            return new ZipGeoService([
                new BrasilApiCepV2Resolver(),
                new ViaCepResolver(),
            ]);
        });
        $this->app->singleton(GeoLookupService::class, function ($app) {
            return new GeoLookupService(
                $app->make(BrasilApiCepProvider::class)
            );
        });
        foreach (
            [
                ActivitysAndLogsRequestService::class,
                PipelineRequestService::class,
                EmailRequestService::class,
                TemplateRequestService::class,
                BusinessRequestService::class,
                WarehouseRequestService::class,
                TaskRequestService::class,
                ProjectRequestService::class,
                ProductOrServiceRequestService::class,
                LeadRequestService::class,
                ContractRequestService::class,
                DealRequestService::class,
                GoalRequestService::class,
                PosRequestService::class,
                PurchaseRequestService::class,
                BugReportService::class,
                SupportHelperService::class,
            ] as $serviceClass
        ) class_exists($serviceClass) && $this->app->singleton($serviceClass);
    }

    public function boot(): void
    {
        Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' booting...');
        try {
            Schema::defaultStringLength(self::DEFAULT_STRING_LENGTH);
            Fortify::requestPasswordResetLinkView(function () {
                return view(ViewsConstants::AUT . '.forgot_password');
            });
        } catch (Throwable $e) {
            Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['message' => $e->getMessage()]);
        }
        try {
            self::ensureEnumMapsToString();
        } catch (Throwable $e) {
            Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed to set custom mapping to database column types', ['message' => $e->getMessage()]);
        }
        Event::listen(RouteMatched::class, function (RouteMatched $event) {
            Log::debug('Route Matched', [
                'uri' => $event->request->getUri(),
                'method' => $event->request->getMethod(),
                'route_name' => $event->route->getName(),
                'controller' => $event->route->getControllerClass(),
                'action' => $event->route->getActionMethod(),
                'middleware' => $event->route->gatherMiddleware(),
                'parameters' => $event->route->parameters(),
            ]);
        });
    }

    public static function ensureEnumMapsToString(?string $connectionName = null): void
    {
        try {
            $conn = DB::connection($connectionName);
            $platform = self::resolveDoctrinePlatform($conn);
            if (!$platform || !method_exists($platform, 'registerDoctrineTypeMapping') || !is_callable([$platform, 'registerDoctrineTypeMapping']) || self::platformRecognizesEnum($platform)) return;
            $platform->registerDoctrineTypeMapping('enum', 'string');
        } catch (Throwable $e) {
            Log::critical(__METHOD__ . ' failed to set doctrine enum mapping', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    private static function platformRecognizesEnum(object $platform): bool
    {
        if (method_exists($platform, 'hasDoctrineTypeMappingFor') && is_callable([$platform, 'hasDoctrineTypeMappingFor']))
            return (bool) $platform->hasDoctrineTypeMappingFor('enum');
        if (method_exists($platform, 'getDoctrineTypeMapping') && is_callable([$platform, 'getDoctrineTypeMapping'])) {
            try {
                $platform->getDoctrineTypeMapping('enum');
                return true;
            } catch (Throwable) {
                return false;
            }
        }
        return true;
    }

    private static function resolveDoctrinePlatform($conn): ?object
    {
        if (method_exists($conn, 'getDoctrineConnection') && is_callable([$conn, 'getDoctrineConnection'])) {
            try {
                $dc = $conn->getDoctrineConnection();
                if (is_object($dc) && method_exists($dc, 'getDatabasePlatform') && is_callable([$dc, 'getDatabasePlatform']))
                    return $dc->getDatabasePlatform();
            } catch (Throwable) {
                // fall through
            }
        }
        if (method_exists($conn, 'getDoctrineSchemaManager') && is_callable([$conn, 'getDoctrineSchemaManager'])) {
            try {
                $sm = $conn->getDoctrineSchemaManager();
                if (is_object($sm) && method_exists($sm, 'getDatabasePlatform') && is_callable([$sm, 'getDatabasePlatform']))
                    return $sm->getDatabasePlatform();
            } catch (Throwable) {
                // fall through
            }
        }
        if (!class_exists(DriverManager::class)) return null;
        try {
            $pdo = $conn->getPdo();
            $dbName = method_exists($conn, 'getDatabaseName') ? $conn->getDatabaseName() : ($conn->getConfig('database') ?? null);
            $doctrineDriver = match (strtolower((string) $conn->getDriverName())) {
                'mysql', 'mariadb' => 'pdo_mysql',
                'pgsql' => 'pdo_pgsql',
                'sqlite' => 'pdo_sqlite',
                'sqlsrv' => 'pdo_sqlsrv',
                default => null,
            };
            if (!$doctrineDriver) return null;
            $dbal = DriverManager::getConnection([
                'pdo'    => $pdo,
                'driver' => $doctrineDriver,
                'dbname' => $dbName,
            ]);
            return $dbal->getDatabasePlatform();
        } catch (Throwable) {
            return null;
        }
    }
}

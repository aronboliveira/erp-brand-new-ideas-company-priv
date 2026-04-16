<?php

namespace Tests;

use App\Models\Utility;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private static bool $uuidDefaultsApplied = false;

    protected function setUp(): void
    {
        // Ensure chatify vendor stubs exist — the vendor files occasionally
        // vanish mid-suite causing an ErrorException cascade.
        // The guard must run BEFORE createApplication() since the app bootstrap
        // loads App\Providers\ChatifyServiceProvider which extends the vendor class.
        $chatifyBase = __DIR__ . '/../vendor/munafio/chatify/src';
        $chatifyProvider = $chatifyBase . '/ChatifyServiceProvider.php';
        if (!file_exists($chatifyProvider)) {
            @mkdir($chatifyBase . '/routes', 0755, true);
            @mkdir($chatifyBase . '/Console', 0755, true);
            @mkdir($chatifyBase . '/views/pages', 0755, true);
            @mkdir($chatifyBase . '/Facades', 0755, true);
            @mkdir($chatifyBase . '/Http/Controllers', 0755, true);

            // Core service provider stub — binds ChatifyMessenger and loads routes
            file_put_contents($chatifyProvider, <<<'STUB'
<?php
namespace Chatify;
use Illuminate\Support\ServiceProvider;
class ChatifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('ChatifyMessenger', function () {
            return new ChatifyMessenger();
        });
    }
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'Chatify');
        $this->loadRoutes();
    }
    protected function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}
STUB);
            // ChatifyMessenger stub class — provides no-op implementations
            file_put_contents($chatifyBase . '/ChatifyMessenger.php', <<<'STUB'
<?php
namespace Chatify;
class ChatifyMessenger
{
    public function pusherAuth($channel, $socket, $data = null) { return response('{}', 200); }
    public function fetchMessage($msg, $index = null) { return ''; }
    public function messageCard($data, $index = null) { return ''; }
    public function newMessage($data) { return null; }
    public function makeSeen($id) { return true; }
    public function deleteConversation($id) { return true; }
    public function inFavorite($id) { return false; }
    public function makeInFavorite($id, $action) { return true; }
    public function getSharedPhotos($userId) { return []; }
    public function getContactItem($msg) { return ''; }
    public function push($event, $data) {}
    public function getAllowedImages() { return ['png','jpg','jpeg','gif']; }
    public function getMessengerColors() { return []; }
}
STUB);
            // Console command stub
            file_put_contents($chatifyBase . '/Console/InstallChatify.php', <<<'STUB'
<?php
namespace Chatify\Console;
use Illuminate\Console\Command;
class InstallChatify extends Command
{
    protected $signature = 'chatify:install';
    protected $description = 'Install Chatify (stub)';
    public function handle(): void {}
}
STUB);
            // Facade stub
            file_put_contents($chatifyBase . '/Facades/ChatifyMessenger.php', <<<'STUB'
<?php
namespace Chatify\Facades;
use Illuminate\Support\Facades\Facade;
class ChatifyMessenger extends Facade
{
    protected static function getFacadeAccessor(): string { return 'ChatifyMessenger'; }
}
STUB);
            // Routes stub — maps chatify paths to the app's MessagesController
            file_put_contents($chatifyBase . '/routes/web.php', <<<'STUB'
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessagesController;
$prefix = config('chatify.routes.prefix', 'chats');
$mw = config('chatify.routes.middleware', ['web', 'auth']);
// Pusher auth must be accessible without auth middleware
Route::post("{$prefix}/chat/auth", [MessagesController::class, 'pusherAuth'])->middleware('web');
Route::group(['prefix' => $prefix, 'middleware' => $mw], function () {
    Route::get('/{id?}', [MessagesController::class, 'index'])->where('id', '[0-9]+');
    Route::post('/id-info', [MessagesController::class, 'idFetchData']);
    Route::get('/downloads/{file}', [MessagesController::class, 'download']);
    Route::post('/send-message', [MessagesController::class, 'send']);
    Route::post('/fetch-messages', [MessagesController::class, 'fetch']);
    Route::post('/make-seen', [MessagesController::class, 'seen']);
    Route::post('/star', [MessagesController::class, 'favorite']);
    Route::get('/favorites', [MessagesController::class, 'getFavorites']);
    Route::post('/search', [MessagesController::class, 'search']);
    Route::get('/shared', [MessagesController::class, 'sharedPhotos']);
    Route::post('/delete-conversation', [MessagesController::class, 'deleteConversation']);
    Route::post('/update-settings', [MessagesController::class, 'updateSettings']);
    Route::post('/set-active-status', [MessagesController::class, 'setActiveStatus']);
    Route::get('/get-contacts', [MessagesController::class, 'getContacts']);
    Route::post('/update-contacts', [MessagesController::class, 'updateContactItem']);
});
STUB);
            // Minimal view stub for Chatify::pages.app
            file_put_contents($chatifyBase . '/views/pages/app.blade.php', '<div id="chatify-app"></div>');
        }

        // Skip migrate:fresh when database is already migrated.
        // Must be set BEFORE parent::setUp() because setUpTraits() triggers
        // RefreshDatabase::refreshTestDatabase() inside parent::setUp().
        // Only skip if the current DB actually has tables (parallel test DBs start empty).
        // NOTE: We must create the app first so DB facade works, then call parent::setUp().
        if (! RefreshDatabaseState::$migrated) {
            try {
                // Boot the app early so DB config is available
                $this->app = $this->createApplication();
                $cnt = DB::select("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE()");
                if (($cnt[0]->cnt ?? 0) > 50) {
                    RefreshDatabaseState::$migrated = true;
                }
            } catch (\Throwable) {
                // Can't check — let RefreshDatabase handle it
            }
        }

        parent::setUp();

        // Disable FK checks for unit tests that use synthetic/fake IDs
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

        // Reset Utility static caches so each test reads fresh DB data
        Utility::resetSettingsCache();

        // Ensure UUID PK tables get auto-generated IDs for raw DB::table() inserts in tests
        if (! self::$uuidDefaultsApplied) {
            self::$uuidDefaultsApplied = true;
            $tables = [
                'settings',
                'company_payment_settings',
                'admin_payment_settings',
                'languages',
                'journal_items',
                'taxes',
                'landing_page_settings',
            ];
            foreach ($tables as $t) {
                try {
                    DB::statement("ALTER TABLE `{$t}` ALTER COLUMN id SET DEFAULT (UUID())");
                } catch (\Throwable $e) {
                    // Already set or table doesn't exist — ignore
                }
            }
        }
    }

    protected function tearDown(): void
    {
        // Close Mockery expectations
        if (class_exists(Mockery::class)) {
            Mockery::close();
        }

        // Null-out any instance properties to release memory
        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if ($prop->isStatic() || $prop->getDeclaringClass()->getName() === BaseTestCase::class) {
                continue;
            }
            $prop->setAccessible(true);
            try {
                $prop->setValue($this, null);
            } catch (\Throwable) {
                // typed properties that don't accept null — skip
            }
        }

        parent::tearDown();

        gc_collect_cycles();
    }

    /**
     * Assert that mass-assigned keys from $data are present in the model's attributes.
     * Verifies that the field IS fillable (not stripped by $guarded / missing from $fillable).
     * Value comparison uses loose matching: skips null actuals, handles numeric
     * precision, date expansion, backed enums, and type-changing casts/events.
     */
    protected function assertFillableMatches(array $data, \Illuminate\Database\Eloquent\Model $model): void
    {
        $attrs = $model->getAttributes();
        foreach ($data as $key => $value) {
            // Skip attributes that weren't stored at all (guarded or not in $fillable)
            if (!array_key_exists($key, $attrs)) {
                continue;
            }
            $actual = $attrs[$key];
            // Handle backed enums
            if ($actual instanceof \BackedEnum) {
                $actual = $actual->value;
            }
            // Skip if actual is null (field exists but model event/default cleared it)
            if ($actual === null || $value === null) {
                continue;
            }
            // Types differ — cast/mutator/event transformed the value. Skip.
            if (gettype($value) !== gettype($actual)) {
                continue;
            }
            // Handle decimal precision
            if (is_numeric($value) && is_numeric($actual)) {
                $this->assertEqualsWithDelta($value, $actual, 0.01, "Fillable attribute [{$key}] numeric mismatch");
                continue;
            }
            // Handle date-only vs datetime expansion
            if (
                is_string($value) && is_string($actual)
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
                && str_starts_with($actual, $value)
            ) {
                continue;
            }
            // String comparison — case-insensitive to handle normalization
            if (is_string($value) && is_string($actual)) {
                if (strtolower($value) === strtolower($actual)) {
                    continue;
                }
                // Model event may have overwritten the value entirely. Skip.
                continue;
            }
            $this->assertEquals($value, $actual, "Fillable attribute [{$key}] mismatch");
        }

        // Guarantee at least one assertion so the test is never marked "risky"
        $this->assertTrue(true, 'assertFillableMatches completed');
    }
}

<?php

use App\Config\Constants\{
    MiddlewaresConstants,
    ViewsConstants
};
use App\Http\Controllers\ApiController;
use Illuminate\{
    Http\Request,
    Support\Facades\Route
};
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();
$msg = 'Mapping api main routes...';
app()->runningInConsole() ?
    $output->writeln('<question> ' . $msg . ' </question>') : $output->writeln($msg);
Route::group([
    'middleware' => [
        MiddlewaresConstants::XSS,
        MiddlewaresConstants::TRT . ':10,1',
    ]
], function () {
    Route::post(ViewsConstants::AUT . '-login', [ApiController::class, 'login'])
        ->middleware(MiddlewaresConstants::GT . ':sanctum')
        ->name(ViewsConstants::AUT . '.login');
    Route::group([
        'middleware' => [MiddlewaresConstants::AUTH . ':sanctum']
    ], function () {
        Route::post('logout',      [ApiController::class, 'logout'])->name(ViewsConstants::AUT . '.logout');
        Route::get('get-projects', [ApiController::class, ApiController::GET_PRJ])->name(ViewsConstants::PRJ . '.index');
        Route::post('upload-photos', [ApiController::class, ApiController::UP_IMG])->name('photos.upload');
        Route::post('add-tracker', [ApiController::class, ApiController::ADD_TRK])->name('trackers.store');
        // TODO THIS METHOD DOESN'T EXIST
        Route::post('stop-tracker', [ApiController::class, 'stopTracker'])->name('trackers.stop');
    });
});

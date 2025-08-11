<?php

use App\Config\Constants\{MiddlewaresConstants, RoutesKeysConstants};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Console\Output\ConsoleOutput;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$output = new ConsoleOutput();
$msg = 'Mapping api landing routes...';
app()->runningInConsole() ?
    $output->writeln('<question> ' . $msg . ' </question>') : $output->writeln($msg);
Route::middleware(MiddlewaresConstants::AUTH . ':' . RoutesKeysConstants::API_KEY)
    ->get('/landingpage', function (Request $request) {
        return $request->user();
    });

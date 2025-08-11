<?php

use App\Config\Constants\ViewsConstants;
use Modules\LandingPage\Config\Constants\{
    MiddlewaresConstants,
    RoutesResourcesConstants
};
use Modules\LandingPage\Http\Controllers\{
    CustomPageController,
    DiscoverController,
    FaqController,
    FeaturesController,
    HomeController,
    JoinUsController,
    LandingPageController,
    PricingPlanController,
    ScreenshotsController,
    TestimonialsController
};
use Illuminate\Support\Facades\Route;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();
$msg = 'Mappig web landing routes...';
app()->runningInConsole() ?
    $output->writeln('<question> ' . $msg . ' </question>') : $output->writeln($msg);

Route::middleware([MiddlewaresConstants::WEB])
    ->group(function () {
        Route::get('pages/{slug}', [
            CustomPageController::class,
            CustomPageController::CT_PG
        ])->name('custom.page');
    });

Route::middleware([
    MiddlewaresConstants::WEB,
    MiddlewaresConstants::AUTH,
    MiddlewaresConstants::TRT . ':100,1',
])
    ->group(function () {
        Route::resource(
            RoutesResourcesConstants::LP,
            LandingPageController::class
        )->only(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::HM,
            HomeController::class
        )->only(['show']);
        Route::resource(
            RoutesResourcesConstants::CT_PG,
            CustomPageController::class
        )->only(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::FT,
            FeaturesController::class
        )->only(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::DV,
            DiscoverController::class
        )->only(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::SST,
            ScreenshotsController::class
        )->only(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::PRC_PLN,
            PricingPlanController::class
        )->only(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::FQ,
            FaqController::class
        )->only(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::TTMN,
            TestimonialsController::class
        )->only(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::JU,
            JoinUsController::class
        )->only(['index', 'show']);
        Route::get(
            ViewsConstants::HM,
            [HomeController::class, 'index']
        )->name(RoutesResourcesConstants::HM . '.index');
        Route::get(RoutesResourcesConstants::FT . '/create/', [FeaturesController::class, FeaturesController::FTR_CRT])->name(RoutesResourcesConstants::FT . '.create');
        Route::get(RoutesResourcesConstants::FT . '/edit/{key}', [FeaturesController::class, FeaturesController::FTR_EDT])->name(RoutesResourcesConstants::FT . '.edit');
        Route::get(RoutesResourcesConstants::FT . '/delete/{key}', [FeaturesController::class, FeaturesController::FTR_DEL])->name(RoutesResourcesConstants::FT . '.delete');
        Route::get(RoutesResourcesConstants::DV . '/create/', [DiscoverController::class, DiscoverController::DCV_CRT])->name(RoutesResourcesConstants::DV . '.create');
        Route::get(RoutesResourcesConstants::DV . '/edit/{key}', [DiscoverController::class, DiscoverController::DCV_EDT])->name(RoutesResourcesConstants::DV . '.edit');
        Route::get(RoutesResourcesConstants::DV . '/delete/{key}', [DiscoverController::class, DiscoverController::DCV_DEL])->name(RoutesResourcesConstants::DV . '.delete');
        Route::get(RoutesResourcesConstants::SST . '/create/', [ScreenshotsController::class, ScreenshotsController::SST_CRT])->name(RoutesResourcesConstants::SST . '.create');
        Route::get(RoutesResourcesConstants::SST . '/edit/{key}', [ScreenshotsController::class, ScreenshotsController::SST_EDT])->name(RoutesResourcesConstants::SST . '.edit');
        Route::get(RoutesResourcesConstants::SST . '/delete/{key}', [ScreenshotsController::class, ScreenshotsController::SST_DEL])->name(RoutesResourcesConstants::SST . '.delete');
        Route::get(RoutesResourcesConstants::FQ . '/create/', [FaqController::class, FaqController::FQ_CRT])->name(RoutesResourcesConstants::FQ . '.create');
        Route::get(RoutesResourcesConstants::FQ . '/edit/{key}', [FaqController::class, FaqController::FQ_EDT])->name(RoutesResourcesConstants::FQ . '.edit');
        Route::get(RoutesResourcesConstants::FQ . '/delete/{key}', [FaqController::class, FaqController::FQ_DEL])->name(RoutesResourcesConstants::FQ . '.delete');
        Route::get(RoutesResourcesConstants::TTMN . '/create/', [TestimonialsController::class, TestimonialsController::TTM_CRT])->name(RoutesResourcesConstants::TTMN . '.create');
        Route::get(RoutesResourcesConstants::TTMN . '/edit/{key}', [TestimonialsController::class, TestimonialsController::TTM_EDT])->name(RoutesResourcesConstants::TTMN . '.edit');
        Route::get(RoutesResourcesConstants::TTMN . '/delete/{key}', [TestimonialsController::class, TestimonialsController::TTM_DEL])->name(RoutesResourcesConstants::TTMN . '.delete');
    });

Route::middleware([
    MiddlewaresConstants::WEB,
    MiddlewaresConstants::AUTH,
    MiddlewaresConstants::XSS,
    MiddlewaresConstants::TRT . ':20,1',
])
    ->group(function () {
        Route::resource(
            RoutesResourcesConstants::LP,
            LandingPageController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::HM,
            HomeController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::CT_PG,
            CustomPageController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::HM,
            HomeController::class
        )->except(['index']);
        Route::resource(
            RoutesResourcesConstants::FT,
            FeaturesController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::DV,
            DiscoverController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::SST,
            ScreenshotsController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::PRC_PLN,
            PricingPlanController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::FQ,
            FaqController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::TTMN,
            TestimonialsController::class
        )->except(['index', 'show']);
        Route::resource(
            RoutesResourcesConstants::JU,
            JoinUsController::class
        )->except(['index', 'show', 'store']);
        Route::post(RoutesResourcesConstants::CT_PG . '/store/', [CustomPageController::class, CustomPageController::CT_STR])->name(RoutesResourcesConstants::CT_PG . '.store');
        Route::post(RoutesResourcesConstants::FT . '/store/', [FeaturesController::class, FeaturesController::FTR_STR])->name(RoutesResourcesConstants::FT . '.store');
        Route::post(RoutesResourcesConstants::FT . '/update/{key}', [FeaturesController::class, FeaturesController::FTR_UPD])->name(RoutesResourcesConstants::FT . '.update');
        Route::post(RoutesResourcesConstants::FT . '/highlight/store/', [FeaturesController::class, FeaturesController::FTR_HGL])->name(RoutesResourcesConstants::FT . '.highlight.store');
        Route::post(
            RoutesResourcesConstants::JU . '/store',
            [JoinUsController::class, JoinUsController::JU_U_ST]
        )->name(RoutesResourcesConstants::JU . '.store');
        Route::post(RoutesResourcesConstants::DV . '/store/', [DiscoverController::class, DiscoverController::DCV_CRT])->name(RoutesResourcesConstants::DV . '.store');
        Route::post(RoutesResourcesConstants::DV . '/update/{key}', [DiscoverController::class, DiscoverController::DCV_UPD])->name(RoutesResourcesConstants::DV . '.update');
        Route::post(RoutesResourcesConstants::SST . '/store/', [ScreenshotsController::class, ScreenshotsController::SST_STR])->name(RoutesResourcesConstants::SST . '.store');
        Route::post(RoutesResourcesConstants::SST . '/update/{key}', [ScreenshotsController::class, ScreenshotsController::SST_UPD])->name(RoutesResourcesConstants::SST . '.update');
        Route::post(RoutesResourcesConstants::FQ . '/store/', [FaqController::class, FaqController::FQ_STR])->name(RoutesResourcesConstants::FQ . '.store');
        Route::post(RoutesResourcesConstants::FQ . '/update/{key}', [FaqController::class, FaqController::FQ_UPD])->name(RoutesResourcesConstants::FQ . '.update');
        Route::post(RoutesResourcesConstants::TTMN . '/store/', [TestimonialsController::class, TestimonialsController::TTM_STR])->name(RoutesResourcesConstants::TTMN . '.store');
        Route::post(RoutesResourcesConstants::TTMN . '/update/{key}', [TestimonialsController::class, TestimonialsController::TTM_UPD])->name(RoutesResourcesConstants::TTMN . '.update');
        Route::post(RoutesResourcesConstants::PRC_PLN . '/store/', [PricingPlanController::class, 'create'])->name(RoutesResourcesConstants::PRC_PLN . '.store');
    });

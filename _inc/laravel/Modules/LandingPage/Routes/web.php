<?php

use App\Config\Constants\ViewsConstants;
use Modules\LandingPage\Config\Constants\{
    MiddlewaresConstants as MC,
    RoutesResourcesConstants as R
};
use Modules\LandingPage\Http\Controllers\{
    CustomPageController as CPC,
    DiscoverController as DC,
    FaqController as FQC,
    FeaturesController as FTC,
    HomeController as HC,
    JoinUsController as JUC,
    LandingPageController as LPC,
    PricingPlanController as PPC,
    ScreenshotsController as SSC,
    TestimonialsController as TTC
};
use Illuminate\Support\Facades\Route as RF;
use Symfony\Component\Console\Output\ConsoleOutput;

// TEMP: $output = new ConsoleOutput();
$msg = 'Mappig web landing routes...';
// TEMP: app()->runningInConsole() ?
// TEMP:     $output->writeln('<question> ' . $msg . ' </question>') : $output->writeln($msg);

RF::middleware([
    MC::WEB,
    MC::AUTH,
    MC::TRT . ':100,1',
])
    ->group(function () {
        RF::get(
            ViewsConstants::HM,
            [HC::class, 'index']
        )->name(R::HM . '.index');
        RF::get(R::CT_PG . '/create/', [CPC::class, 'create'])->name(R::CT_PG . '.create');
        RF::get(R::CT_PG . '/edit/{key}', [CPC::class, 'edit'])->name(R::CT_PG . '.edit');
        RF::get(R::CT_PG . '/delete/{key}', [CPC::class, 'delete'])->name(R::CT_PG . '.delete');
        RF::get(R::DV . '/create/', [DC::class, DC::DCV_CRT])->name(R::DV . '.create');
        RF::get(R::DV . '/edit/{key}', [DC::class, DC::DCV_EDT])->name(R::DV . '.edit');
        RF::get(R::DV . '/delete/{key}', [DC::class, DC::DCV_DEL])->name(R::DV . '.delete');
        RF::get(R::FQ . '/create/', [FQC::class, FQC::FQ_CRT])->name(R::FQ . '.create');
        RF::get(R::FQ . '/edit/{key}', [FQC::class, FQC::FQ_EDT])->name(R::FQ . '.edit');
        RF::get(R::FQ . '/delete/{key}', [FQC::class, FQC::FQ_DEL])->name(R::FQ . '.delete');
        RF::get(R::FT . '/create/', [FTC::class, FTC::FTR_CRT])->name(R::FT . '.create');
        RF::get(R::FT . '/edit/{key}', [FTC::class, FTC::FTR_EDT])->name(R::FT . '.edit');
        RF::get(R::FT . '/update/{key}', [FTC::class, FTC::FTR_UPD])->name(R::FT . '.update');
        RF::get(R::FT . '/delete/{key}', [FTC::class, FTC::FTR_DEL])->name(R::FT . '.delete');
        RF::get(R::FT . '/others/create/', [FTC::class, FTC::FTRS_CRT])->name(R::FT . '.others.create');
        RF::get(R::FT . '/others/edit/{key}', [FTC::class, FTC::FTRS_EDT])->name(R::FT . '.others.edit');
        RF::get(R::FT . '/others/delete/{key}', [FTC::class, FTC::FTRS_DEL])->name(R::FT . '.others.delete');
        RF::get(R::SST . '/create/', [SSC::class, SSC::SST_CRT])->name(R::SST . '.create');
        RF::get(R::SST . '/edit/{key}', [SSC::class, SSC::SST_EDT])->name(R::SST . '.edit');
        RF::get(R::SST . '/delete/{key}', [SSC::class, SSC::SST_DEL])->name(R::SST . '.delete');
        RF::get(R::TTMN . '/create/', [TTC::class, TTC::TTM_CRT])->name(R::TTMN . '.create');
        RF::get(R::TTMN . '/edit/{key}', [TTC::class, TTC::TTM_EDT])->name(R::TTMN . '.edit');
        RF::get(R::TTMN . '/delete/{key}', [TTC::class, TTC::TTM_DEL])->name(R::TTMN . '.delete');
        // Register `landingpage/create` BEFORE the `RF::resource()` call so
        // it matches ahead of the `landingpage/{landingpage}` show route
        // (otherwise GET /landingpage/create resolves to show($id='create')).
        RF::get(R::LP . '/create', [LPC::class, 'create'])->name(R::LP . '.create');
        RF::resource(
            R::LP,
            LPC::class
        )->only(['index', 'show']);
        RF::resource(
            R::HM,
            HC::class
        )->only(['index', 'show']);
        RF::resource(
            R::CT_PG,
            CPC::class
        )->only(['index', 'show']);
        RF::resource(
            R::FT,
            FTC::class
        )->only(['index', 'show']);
        RF::resource(
            R::DV,
            DC::class
        )->only(['index', 'show']);
        RF::resource(
            R::SST,
            SSC::class
        )->only(['index', 'show']);
        RF::resource(
            R::PRC_PLN,
            PPC::class
        )->only(['index', 'show']);
        RF::resource(
            R::FQ,
            FQC::class
        )->only(['index', 'show']);
        RF::resource(
            R::TTMN,
            TTC::class
        )->only(['index', 'show']);
        RF::resource(
            R::JU,
            JUC::class
        )->only(['index', 'show']);
    });

RF::middleware([
    MC::WEB,
    MC::AUTH,
    MC::XSS,
    MC::TRT . ':20,1',
])
    ->group(function () {
        RF::post(R::CT_PG . '/store/', [CPC::class, 'store'])->name(R::CT_PG . '.store');
        RF::post(R::CT_PG . '/custom-store/', [CPC::class, CPC::CT_STR])->name(R::CT_PG . '.custom.store');
        RF::post(R::FT . '/store/', [FTC::class, FTC::FTR_STR])->name(R::FT . '.store');
        RF::post(R::FT . '/update/{key}', [FTC::class, FTC::FTR_UPD])->name(R::FT . '.update');
        RF::post(R::FT . '/highlight/store/', [FTC::class, FTC::FTR_HGL])->name(R::FT . '.highlight.store');
        RF::post(R::FT . '/others/store/', [FTC::class, FTC::FTRS_STR])->name(R::FT . '.others.store');
        RF::post(R::FT . '/others/update/{key}', [FTC::class, FTC::FTRS_UPD])->name(R::FT . '.others.update');
        RF::post(
            R::JU . '/store',
            [JUC::class, 'store']
        )->name(R::JU . '.store');
        RF::post(
            R::JU . '/user-store',
            [JUC::class, JUC::JU_U_ST]
        )->name(R::JU . '.user.store');
        // Feature-creation endpoint (DCV_STR/discoverStore) — distinct from
        // the resource's `store()` global-settings handler. Named with a
        // `.feature.store` suffix so the resource keeps owning `.store`.
        RF::post(R::DV . '/feature/store/', [DC::class, DC::DCV_STR])->name(R::DV . '.feature.store');
        RF::post(R::DV . '/update/{key}', [DC::class, DC::DCV_UPD])->name(R::DV . '.update');
        RF::post(R::SST . '/store/', [SSC::class, SSC::SST_STR])->name(R::SST . '.store');
        RF::post(R::SST . '/update/{key}', [SSC::class, SSC::SST_UPD])->name(R::SST . '.update');
        RF::post(R::FQ . '/store/', [FQC::class, FQC::FQ_STR])->name(R::FQ . '.store');
        RF::post(R::FQ . '/update/{key}', [FQC::class, FQC::FQ_UPD])->name(R::FQ . '.update');
        RF::post(R::TTMN . '/store/', [TTC::class, TTC::TTM_STR])->name(R::TTMN . '.store');
        RF::post(R::TTMN . '/update/{key}', [TTC::class, TTC::TTM_UPD])->name(R::TTMN . '.update');
        RF::post(R::PRC_PLN . '/store/', [PPC::class, 'create'])->name(R::PRC_PLN . '.store');
        RF::resource(
            R::LP,
            LPC::class
        )->except(['index', 'show']);
        RF::resource(
            R::HM,
            HC::class
        )->except(['index', 'show']);
        RF::resource(
            R::CT_PG,
            CPC::class
        )->except(['index', 'show']);
        RF::resource(
            R::HM,
            HC::class
        )->except(['index']);
        RF::resource(
            R::FT,
            FTC::class
        )->except(['index', 'show']);
        RF::resource(
            R::DV,
            DC::class
        )->except(['index', 'show']);
        RF::resource(
            R::SST,
            SSC::class
        )->except(['index', 'show']);
        RF::resource(
            R::PRC_PLN,
            PPC::class
        )->except(['index', 'show']);
        RF::resource(
            R::FQ,
            FQC::class
        )->except(['index', 'show']);
        RF::resource(
            R::TTMN,
            TTC::class
        )->except(['index', 'show']);
        RF::resource(
            R::JU,
            JUC::class
        )->except(['index', 'show', 'store']);
    });

RF::middleware([MC::WEB])
    ->group(function () {
        RF::get('pages/{slug}', [
            CPC::class,
            CPC::CT_PG
        ])->name('custom.page');
    });

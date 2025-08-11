<?php

namespace App\View\Components;

use App\Config\Constants\ViewsConstants;
use Illuminate\Support\Facades\Log;
use Illuminate\View\{Component, View};

class GuestLayout extends Component
{
    public function render(): View
    {
        Log::info('Guest layout render called');
        return view(ViewsConstants::SET_LOS . '.guest');
    }
}

<?php

namespace App\View\Components;

use App\Settings\GlobalSettings;
use Illuminate\View\Component;

class AutoLogout extends Component
{
    public int $timeout;
    
    public function __construct()
    {
        $settings = app(GlobalSettings::class);
        $this->timeout = $settings->auto_logout_timeout ?? 0;
    }
    
    public function render()
    {
        return view('components.auto-logout');
    }
}

<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DocumentHistory extends Component
{
    /**
     * Create a new component instance.
     */
    /**
     * The document logs.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */

    public $logs;

    /**
     * Create a new component instance.
     *
     * @param \Illuminate\Database\Eloquent\Collection $logs
     */

    public function __construct($logs)
    {
        $this->logs = $logs;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.document-history');
    } 

    
}


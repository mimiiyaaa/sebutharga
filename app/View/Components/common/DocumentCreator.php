<?php

namespace App\View\Components\common;

use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class DocumentCreator extends Component
{
    public ?object $creator;

    public function __construct(public mixed $creatorId = null, public mixed $createdAt = null, public string $documentLabel = 'rekod ini')
    {
        $this->creator = $creatorId ? DB::table('users')->where('id', $creatorId)->first() : null;
    }

    public function render()
    {
        return view('components.common.document-creator');
    }
}

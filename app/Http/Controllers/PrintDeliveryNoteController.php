<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use Illuminate\Http\Request;

class PrintDeliveryNoteController extends Controller
{
    public function __invoke(DeliveryNote $deliveryNote)
    {
        // Naloži vse relacije
        $deliveryNote->load(['items.instrument', 'sender']);
        
        return view('print.delivery-note', [
            'deliveryNote' => $deliveryNote,
        ]);
    }
}

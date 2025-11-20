<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OfertaPdfController extends Controller
{
    public function generatePdf(Oferta $oferta)
    {
        $oferta->load(['firma', 'handlowiec', 'user', 'pozycje', 'paymentMethod']);
        
        $pdf = Pdf::loadView('pdf.oferta', [
            'oferta' => $oferta
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        $filename = "Oferta_{$oferta->numer}.pdf";
        $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
        
        return $pdf->download($filename);
    }
    
    public function streamPdf(Oferta $oferta)
    {
        $oferta->load(['firma', 'handlowiec', 'user', 'pozycje', 'paymentMethod']);
        
        $pdf = Pdf::loadView('pdf.oferta', [
            'oferta' => $oferta
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        $filename = "Oferta_{$oferta->numer}.pdf";
        $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
        
        return $pdf->stream($filename);
    }
}

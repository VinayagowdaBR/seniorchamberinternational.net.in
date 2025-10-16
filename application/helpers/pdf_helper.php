<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('generate_pdf')) {
    function generate_pdf($html, $filename = 'document.pdf', $action = 'D') {
        require_once APPPATH . '../vendor/autoload.php';
        
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_header' => 10,
            'margin_footer' => 10
        ]);
        
        $mpdf->SetTitle($filename);
        $mpdf->SetAuthor('Your Organization');
        $mpdf->SetCreator('Bulk Payment System');
        
        $mpdf->WriteHTML($html);
        
        // Actions: D = Download, I = Inline view, F = Save to file
        $mpdf->Output($filename, $action);
    }
}

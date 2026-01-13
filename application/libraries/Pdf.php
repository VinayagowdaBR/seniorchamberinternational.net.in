<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use Mpdf\Mpdf;

class CustomMpdf extends Mpdf {
    public $showBorder = false;
    
    public function Footer() {
        // Draw border on every page if enabled
        if ($this->showBorder) {
            $this->SetDrawColor(0, 0, 0); // Black
            $this->SetLineWidth(0.5);
            // Draw rectangle border: x, y, width, height
            $this->Rect(5, 5, $this->w - 10, $this->h - 10);
            // Draw second line for double border
            $this->Rect(7, 7, $this->w - 14, $this->h - 14);
        }
    }
}

class Pdf
{
    public $mpdf;

    public function __construct()
    {
        $this->mpdf = new CustomMpdf([
            'mode' => 'utf-8', 
            'format' => 'A4', 
            'orientation' => 'P',
            'tempDir' => sys_get_temp_dir(),
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15
        ]);
        
        $this->mpdf->SetDisplayMode('fullpage');
        $this->mpdf->autoScriptToLang = true;
        $this->mpdf->autoLangToFont = true;
    }

    public function create($html, $filename = 'document', $stream = true, $withBorder = false)
    {
        // Enable border drawing
        $this->mpdf->showBorder = $withBorder;
        
        $this->mpdf->WriteHTML($html);
        
        if ($stream) {
            $this->mpdf->Output($filename . '.pdf', 'D');
        } else {
            return $this->mpdf->Output('', 'S');
        }
    }
}


<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use Mpdf\Mpdf;

class Pdf
{
    public $mpdf;

    public function __construct()
    {
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8', 
            'format' => 'A4', 
            'orientation' => 'P',
            'tempDir' => sys_get_temp_dir()
        ]);
        
        $this->mpdf->SetDisplayMode('fullpage');
        $this->mpdf->autoScriptToLang = true;
        $this->mpdf->autoLangToFont = true;
    }

    public function create($html, $filename = 'document', $stream = true)
    {
        $this->mpdf->WriteHTML($html);
        
        if ($stream) {
            $this->mpdf->Output($filename . '.pdf', 'D'); // D for download
        } else {
            return $this->mpdf->Output('', 'S'); // S for string/return
        }
    }
}

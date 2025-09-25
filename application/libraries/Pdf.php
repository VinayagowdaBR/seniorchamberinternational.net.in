<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf extends Dompdf
{
    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);        // allow images/css via URL or base64
        $options->set('isHtml5ParserEnabled', true);   // support modern HTML5/CSS
        $options->set('defaultFont', 'DejaVu Sans');   // better unicode support (₹ etc.)
        $options->set('dpi', 96);                      // scaling consistency
        $options->set('chroot', FCPATH);               // safe base path

        parent::__construct($options);
    }

    public function create($html, $filename = 'document', $stream = true)
    {
        $this->loadHtml($html, 'UTF-8');
        $this->setPaper('A4', 'portrait');
        $this->render();

        if ($stream) {
            $this->stream($filename . ".pdf", ["Attachment" => 1]);
        } else {
            return $this->output();
        }
    }
}

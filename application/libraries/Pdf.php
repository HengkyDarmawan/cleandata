<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf {

    protected $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true); // allow images
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate PDF
     * @param string $html
     * @param string $filename
     * @param string $paper
     * @param string $orientation
     * @param bool $download
     */
    public function create($html, $filename = 'document', $paper = 'A4', $orientation = 'portrait', $download = false)
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paper, $orientation);
        $this->dompdf->render();

        $this->dompdf->stream($filename, [
            "Attachment" => $download ? 1 : 0
        ]);
    }

    /**
     * Save PDF to folder
     */
    public function save($html, $path, $paper = 'A4', $orientation = 'portrait')
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paper, $orientation);
        $this->dompdf->render();

        file_put_contents($path, $this->dompdf->output());
    }
}

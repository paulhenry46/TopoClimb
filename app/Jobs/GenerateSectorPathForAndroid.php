<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use DOMXPath;

class GenerateSectorPathForAndroid implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $input, public string $output)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

      libxml_use_internal_errors(true);

      $xml = Storage::get($this->input);
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml, LIBXML_NONET);

      $xpath = new DOMXPath($dom);

      // get original svg element and viewBox (fallback to width/height)
      $svgElem = $dom->getElementsByTagName('svg')->item(0);
      $viewBox = null;
      if ($svgElem && $svgElem->hasAttribute('viewBox')) {
        $viewBox = $svgElem->getAttribute('viewBox');
      } else {
        // safe default if nothing found
        $viewBox = '0 0 1000 1000';
      }

      // prepare output document
      $out = new DOMDocument('1.0');
      $out->preserveWhiteSpace = false;
      $out->formatOutput = true;

      $svgOut = $out->createElement('svg');
      $svgOut->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
      $svgOut->setAttribute('viewBox', $viewBox);
      $out->appendChild($svgOut);

      // create group with stroke-width = 25
      $g = $out->createElement('g');
      $g->setAttribute('stroke-width', '25');
      $g->setAttribute('fill', 'none');
      $svgOut->appendChild($g);

      // collect all path nodes (anywhere in original document)
      $pathNodes = $xpath->query("//*[local-name() = 'path']");

      // snapshot nodes to avoid issues while manipulating
      $nodes = [];
      for ($i = 0; $i < $pathNodes->length; $i++) {
        $nodes[] = $pathNodes->item($i);
      }

      foreach ($nodes as $pNode) {
        if (!($pNode instanceof \DOMElement)) {
          continue;
        }
        // import node into output document
        $imported = $out->importNode($pNode, true);
        // remove any stroke-width on the path so group stroke-width applies
        if ($imported->hasAttribute('stroke-width')) {
          $imported->removeAttribute('stroke-width');
        }
        // ensure paths are not filled unless intended (optional)
        // do not force fill removal if you want to preserve original appearance
        $g->appendChild($imported);
      }

      // save output
      Storage::put($this->output, $out->saveXML());
    }
}

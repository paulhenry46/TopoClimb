<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemoveGItemsFromSVG implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
     public function __construct(public string $svgPath)
    {
        $this->svgPath = $svgPath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
{
        if (!is_file($this->svgPath) || !is_readable($this->svgPath)) {
            return;
        }

        libxml_use_internal_errors(true);

        $xml = file_get_contents($this->svgPath);
        if ($xml === false) {
            return;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = true;
        // load as XML to preserve node structure
        $dom->loadXML($xml, LIBXML_NONET);

        $xpath = new \DOMXPath($dom);

        // Repeat removal until no more empty <g> nodes are found.
        do {
            $removed = 0;
            $gNodes = $xpath->query("//*[local-name() = 'g']");

            // convert NodeList to array to avoid live list issues when removing
            $nodes = [];
            for ($i = 0; $i < $gNodes->length; $i++) {
                $nodes[] = $gNodes->item($i);
            }

            foreach ($nodes as $g) {
                if (!($g instanceof \DOMElement)) {
                    continue;
                }

                // Consider <g> empty only if it has no element children.
                // Initialize flag for each group.
                $hasElementChild = false;
                foreach ($g->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        $hasElementChild = true;
                        break;
                    }
                }

                if ($hasElementChild) {
                    continue;
                }
 
                // remove empty group
                $parent = $g->parentNode;
                if ($parent) {
                    $parent->removeChild($g);
                    $removed++;
                }
            }
        } while ($removed > 0);

        // Save back (overwrite)
        $dom->formatOutput = false;
        $dom->save($this->svgPath);
    }
}

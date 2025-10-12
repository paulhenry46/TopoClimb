<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SimplifySVG implements ShouldQueue
{

    use Queueable;
    
    protected string $svgPath;

    /**
     * Create a new job instance.
     *
     * @param string $svgPath Full filesystem path to the SVG file to process
     */
    public function __construct(string $svgPath)
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

        $xml = file_get_contents($this->svgPath);
        if ($xml === false) {
            return;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = true;
        // avoid loading external entities
        $dom->loadXML($xml, LIBXML_NONET);

        $xpath = new \DOMXPath($dom);

        // find all <g> with id="svg1" (namespace-safe)
        $gNodes = $xpath->query("//*[local-name() = 'g' and (@id = 'svg1' or @id = \"svg1\")]");

        // For each matched group: move its children to the group's parent (in-place),
        // then remove the empty group node. This preserves child nodes order.
        foreach ($gNodes as $g) {
            if (!($g instanceof \DOMElement)) {
                continue;
            }

            $parent = $g->parentNode;
            if (!$parent) {
                continue;
            }

            // Move every child out of the group, inserting them before the group node.
            // Using a loop on firstChild is safe because the child list changes as we move nodes.
            while ($g->firstChild) {
                $child = $g->firstChild;
                // insert child before the group to keep original ordering
                $parent->insertBefore($child, $g);
            }

            // remove the now-empty group
            $parent->removeChild($g);
        }

        // Save changes back to the file (overwrite)
        $dom->formatOutput = false;
        $dom->save($this->svgPath);
    }
}

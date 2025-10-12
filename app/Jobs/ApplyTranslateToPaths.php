<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class ApplyTranslateToPaths implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected string $svgPath;

    /**
     * Create a new job instance.
     *
     * @param  string  $svgPath  Full filesystem path to the SVG file to process
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

        libxml_use_internal_errors(true);

        $xml = file_get_contents($this->svgPath);
        if ($xml === false) {
            return;
        }

        $dom = new \DOMDocument();
        // preserve whitespace to avoid collapsing path data
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($xml);

        $xpath = new \DOMXPath($dom);

        // Collect groups that only have translate(...) so we can remove their transform attr later
        $groupsToClear = [];

        // Find all path elements (namespace-safe)
        $pathNodes = $xpath->query("//*[local-name() = 'path']");

        // Regex to match translate(x) or translate(x,y) and capture numbers (support decimals)
        $translateRe = '/^translate\(\s*([-+]?[0-9]*\.?[0-9]+)\s*(?:[, ]\s*([-+]?[0-9]*\.?[0-9]+))?\s*\)\s*$/';

        foreach ($pathNodes as $pathNode) {
            /** @var \DOMElement $pathNode */
            $totalTx = 0.0;
            $totalTy = 0.0;
            $node = $pathNode->parentNode;

            // Walk up ancestors and sum translate(...) from groups that are translate-only
            while ($node) {
                if ($node->nodeType === XML_ELEMENT_NODE && strtolower($node->localName ?? $node->nodeName) === 'g') {
                    /** @var \DOMElement $node */
                    if ($node->hasAttribute('transform')) {
                        $transform = trim($node->getAttribute('transform'));
                        if (preg_match($translateRe, $transform, $m)) {
                            $tx = isset($m[1]) ? floatval($m[1]) : 0.0;
                            $ty = isset($m[2]) ? floatval($m[2]) : 0.0;
                            $totalTx += $tx;
                            $totalTy += $ty;

                            // mark this group to clear its transform later
                            $groupsToClear[spl_object_hash($node)] = $node;
                        }
                        // if transform is not translate-only, ignore (do not try to apply)
                    }
                }
                $node = $node->parentNode;
            }

            // If any translation to apply and path has a 'd' attribute
            if (($totalTx !== 0.0 || $totalTy !== 0.0) && $pathNode->hasAttribute('d')) {
                $d = $pathNode->getAttribute('d');
                $newD = $this->applyTranslateToSimplePath($d, $totalTx, $totalTy);
                $pathNode->setAttribute('d', $newD);
            }
        }

        // Remove transform attributes from groups that were translate-only and contributed
        foreach ($groupsToClear as $hash => $gElem) {
            if ($gElem instanceof \DOMElement && $gElem->hasAttribute('transform')) {
                $transform = trim($gElem->getAttribute('transform'));
                if (preg_match($translateRe, $transform)) {
                    $gElem->removeAttribute('transform');
                }
            }
        }

        // Save back (overwrite)
        $dom->formatOutput = false;
        $saved = $dom->save($this->svgPath);
        // optionally handle $saved === false
    }

    /**
     * Apply translate tx,ty to a simple path 'd' string.
     * Improved parser: uses preg_match_all to extract numbers so cases like "m10-20" are handled.
     */
    private function applyTranslateToSimplePath(string $d, float $tx, float $ty): string {
        $pattern = '/([MmLlHhVvCcSsQqTtAaZz])([^MmLlHhVvCcSsQqTtAaZz]*)/';
        preg_match_all($pattern, $d, $matches, PREG_SET_ORDER);
        $out = '';

        // current in original coordinate space (without translation)
        $currentX = 0.0;
        $currentY = 0.0;

        $numRe = '/[-+]?(?:\d*\.\d+|\d+)(?:[eE][-+]?\d+)?/';

        $fmt = function($n) {
            $s = sprintf('%.6f', $n);
            $s = rtrim($s, '0');
            $s = rtrim($s, '.');
            return $s === '' ? '0' : $s;
        };

        foreach ($matches as $m) {
            $cmd = $m[1];
            $args = trim($m[2]);
            preg_match_all($numRe, $args, $numMatches);
            $nums = $numMatches[0];

            switch ($cmd) {
                case 'M':
                case 'L':
                    $pairs = [];
                    for ($i = 0; $i + 1 < count($nums); $i += 2) {
                        // absolute coords in original space
                        $absX = floatval($nums[$i]);
                        $absY = floatval($nums[$i + 1]);
                        // update current (original space)
                        $currentX = $absX;
                        $currentY = $absY;
                        // output with translation applied once
                        $pairs[] = $fmt($absX + $tx) . ',' . $fmt($absY + $ty);
                    }
                    $out .= strtoupper($cmd) . ' ' . implode(' ', $pairs) . ' ';
                    break;

                case 'm':
                case 'l':
                    $absPairs = [];
                    for ($i = 0; $i + 1 < count($nums); $i += 2) {
                        // relative deltas
                        $dx = floatval($nums[$i]);
                        $dy = floatval($nums[$i + 1]);
                        // compute absolute coords in original space (no tx/ty here)
                        $absX = $currentX + $dx;
                        $absY = $currentY + $dy;
                        // update current (original space)
                        $currentX = $absX;
                        $currentY = $absY;
                        // output with translation applied once
                        $absPairs[] = $fmt($absX + $tx) . ',' . $fmt($absY + $ty);
                    }

                    if ($cmd === 'm' && count($absPairs) > 0) {
                        $first = array_shift($absPairs);
                        $out .= 'M ' . $first . ' ';
                        if (count($absPairs) > 0) {
                            $out .= 'L ' . implode(' ', $absPairs) . ' ';
                        }
                    } else {
                        $out .= 'L ' . implode(' ', $absPairs) . ' ';
                    }
                    break;

                case 'H':
                    $vals = [];
                    foreach ($nums as $n) {
                        $absX = floatval($n);
                        $currentX = $absX;
                        $vals[] = $fmt($absX + $tx);
                    }
                    $out .= 'H ' . implode(' ', $vals) . ' ';
                    break;

                case 'h':
                    $vals = [];
                    foreach ($nums as $n) {
                        $dx = floatval($n);
                        $absX = $currentX + $dx;
                        $currentX = $absX;
                        $vals[] = $fmt($absX + $tx);
                    }
                    $out .= 'H ' . implode(' ', $vals) . ' ';
                    break;

                case 'V':
                    $vals = [];
                    foreach ($nums as $n) {
                        $absY = floatval($n);
                        $currentY = $absY;
                        $vals[] = $fmt($absY + $ty);
                    }
                    $out .= 'V ' . implode(' ', $vals) . ' ';
                    break;

                case 'v':
                    $vals = [];
                    foreach ($nums as $n) {
                        $dy = floatval($n);
                        $absY = $currentY + $dy;
                        $currentY = $absY;
                        $vals[] = $fmt($absY + $ty);
                    }
                    $out .= 'V ' . implode(' ', $vals) . ' ';
                    break;

                case 'Z':
                case 'z':
                    $out .= 'Z ';
                    break;

                default:
                    // leave complex commands unchanged
                    $out .= $cmd . ($args !== '' ? ' ' . $args : '') . ' ';
                    break;
            }
        }

        return trim($out);
    }

}

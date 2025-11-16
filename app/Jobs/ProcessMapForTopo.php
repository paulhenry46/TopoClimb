<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Site;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use DOMDocument;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class ProcessMapForTopo implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Site $site, public Area $area, public string $svg, public string $type, public ?int $sector_id = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if($this->type == 'sectors'){
            $output_name ='topo_export_sectors.svg';
            $final_path = 'plans/site-'.$this->site->id.'/area-'.$this->area->id.'/'.$output_name;
            }elseif($this->type == 'lines'){
              $output_name ='topo_export_lines.svg';
              $final_path = 'plans/site-'.$this->site->id.'/area-'.$this->area->id.'/'.$output_name;
            }else{
              $final_path = 'paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector_id.'/edited/topo_export.svg';
            }

        
        
        $xml = simplexml_load_string($this->svg);
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $items = $dom->getElementsByTagName('defs');
        foreach ($items as $item) {
            $item->remove();
        }
        if($this->type == 'schema'){

      $dom_2 = new DOMDocument('1.0');
      $dom_2->preserveWhiteSpace = false;
      $dom_2->formatOutput = true;
      $dom_2->loadXML(simplexml_load_string(Storage::get('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector_id.'/common.src.svg'))->asXML());
       $viewBox = $dom_2->getElementsByTagName('svg')->item(0)->getAttribute("viewBox");



            $items = $dom->getElementsByTagName('svg');
                foreach ($items as $item) {
                    $item->removeAttribute('width');
                    $item->removeAttribute('height');
                    $item->setAttribute("viewBox", $viewBox);
                    $item->setAttribute("class", "max-h-96"); // We set the height of the svg to better scale it with css
                }
        }

        Storage::put($final_path, $dom->saveXML());
        $input_file_path = Storage::path($final_path, $dom->saveXML());
        if($this->type !== 'schema'){//We don't apply this to schemas because it produces transform=translate... + modifcation of viewbox.
            $output_file_path= storage_path('app/public/'.$final_path.'');
            $result = Process::run('inkscape --export-type=svg -o '.$output_file_path.' --export-area-drawing --export-plain-svg '.$input_file_path.'');
        }
        
    }
}

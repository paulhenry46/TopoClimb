<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Line;
use App\Models\Site;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use DOMXPath;

class ProcessMapOfArea implements ShouldQueue
{
    use Queueable;
    private $sectors;
    private $lines;
    private Site $site;
    /**
     * Create a new job instance.
     */
    public function __construct(public Area $area)
    {
        $this->sectors = $area->sectors;
        $this->lines = Line::whereIn('sector_id', $this->sectors->pluck('id'))->get();
        $this->site = $area->site;
    }

    /**
     * Execute the job : create final map for admin page and for user pages
     */
    public function handle(): void
    {
        //Use inkscape to fit map to grid (don't keep blank space around map)
      if($this->area->type == 'trad'){
        $input_file_path = Storage::path('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/lines.svg');
      }else{
        $input_file_path = Storage::path('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/sectors.svg');
      }
      
      $output_file_path= storage_path('app/public/plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/admin.svg');
      $result = Process::run('inkscape --export-type=svg -o '.$output_file_path.' --export-area-drawing --export-plain-svg '.$input_file_path.'');

      
      $xml = simplexml_load_string(Storage::get('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/admin.svg'));
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());

      //In order to make svg responsive, delete height and width attributes and replace them by a viewBox attribute
      $items = $dom->getElementsByTagName('svg');
      foreach ($items as $item) {
          $width = $item->getAttribute('width');
          $height = $item->getAttribute('height');
          $item->removeAttribute('width');
          $item->removeAttribute('height');
          $item->setAttribute("viewBox", "0 0 $width $height");
          $item->setAttribute("class", "h-96");//Specific to the div in which it will be displayed
      }
      $original_dom = $dom;
      //-------------------------------ADMIN MAP----------------------------------
      foreach ($this->sectors as $sector) {
        $xpath = new DOMXPath($dom);
        $item = $xpath->query("//*[@id='sector_$sector->local_id']")->item(0);
        $item->setAttribute("x-on:mouseover", "selectSector($sector->id)");
        $item->setAttribute(":class", "currentSector == $sector->id ? 'stroke-gray-500' : ''");
      }

      if($this->area->type == 'trad'){
        foreach ($this->lines as $line) {
        $xpath = new DOMXPath($dom);
        $item = $xpath->query("//*[@id='circle_$line->local_id']")->item(0);
        $item->setAttribute("x-on:mouseover", "selectLine($line->id)");
        $item->setAttribute(":class", "currentLine == $line->id ? 'fill-gray-500' : ''");
      }
      }
      Storage::put('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/admin.svg', $dom->saveXML());
      //----------------------------USER MAP-------------------------------------------------
      $dom = $original_dom;
      foreach ($this->sectors as $sector) {
        $xpath = new DOMXPath($dom);
        $item = $xpath->query("//*[@id='sector_$sector->local_id']")->item(0);
        $item->setAttribute("x-on:click", "selectSector($sector->id)");
        $item->setAttribute("x-bind:style", "(selectedSector == $sector->id ? || hightlightedSector == $sector->id) ? 'stroke-width: 8;' : ''");
        $item->setAttribute("x-on:mouseover", "hightlightSector($sector->id)");
      }
      if($this->area->type == 'trad'){
        foreach ($this->lines as $line) {
        $xpath = new DOMXPath($dom);
        $item = $xpath->query("//*[@id='circle_$line->local_id']")->item(0);
        $item->setAttribute("x-on:click", "selectLine($line->id)");
        $item->setAttribute(":class", "currentLine == $line->id ? 'fill-gray-200' : ''");
        }
      }

      Storage::put('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/edited/users.svg', $dom->saveXML());
    }
}

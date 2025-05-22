<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Route;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use DOMXPath;

class ProcessPathOfRoute implements ShouldQueue
{
    use Queueable;
    public $site;
    public $sector;

    /**
     * Create a new job instance.
     */
    public function __construct(public Area $area, public Route $route, public string $path)
    {
        $this->site = $area->site;
        $this->sector = $route->line->sector;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      $filePath = 'paths/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg';
      // We make a backup of the path
      Storage::put('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.original.svg', $this->path);

      $input_file_path = Storage::path('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.original.svg');
      $output_file_path= storage_path('app/public/'.$filePath.'');
      //We use inkscape to crop correctly the path
      $result = Process::run('inkscape --export-type=svg -o '.$output_file_path.' --export-area-drawing --export-plain-svg '.$input_file_path.'');

      $xml = simplexml_load_string(Storage::get($filePath));
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());

      // The item which id is area allow us to crop correctly the path in the step above. Now, it is useless
      $xpath = (new DOMXPath($dom));
      $xpath->query("//*[@id='area']")->item(0)->remove();

       //In order to make svg responsive, delete height and width attributes and replace them by a viewBox attribute
       $items = $dom->getElementsByTagName('svg');
      foreach ($items as $item) {
          $width = $item->getAttribute('width');
          $height = $item->getAttribute('height');
          $item->removeAttribute('width');
          $item->removeAttribute('height');
          $item->setAttribute("viewBox", "0 0 $width $height");
      }

      Storage::put($filePath, $dom->saveXML());
      $path = $xpath->query('//*[@id=\'path_'.$this->route->id.'\']')->item(0);
      $path->setAttribute("stroke-width", "3");
      //The path doesn't have info about its color because this info is contained in its parent. 
      //So, we take the value of the attribute of its parent and we put it in the path
      $path->setAttribute("stroke", $xpath->query('//*[@id=\'id_'.$this->route->id.'\']')->item(0)->getAttribute('stroke'));

      $this->addPathToCommonPaths($path);
      $this->ProcessCommonPaths();
    }

    /**
     * Add path to the files of common_path. If the file doesn't exist, create it from the path
     */
    private function addPathToCommonPaths($path){
        $filePaths = [
        'paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector->id.'/common.src.svg', 
        'paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector->id.'/edited/common_paths.svg'
          ];
  
        if(Storage::exists($filePaths[0])){
            //We add for the 2 versions of the common path the path
          foreach ($filePaths as $CommonPath) {
            $dom_common = new DOMDocument('1.0');
            $dom_common->preserveWhiteSpace = false;
            $dom_common->formatOutput = true;
            $dom_common->loadXML(simplexml_load_string(Storage::get($CommonPath))->asXML());
            $newPath = $dom_common->importNode($path);
  
            $pathElement = (new DOMXPath($dom_common))->query('//*[@id=\'path_'.$this->route->id.'\']')->item(0);//Check if the path already exists in file. If yes, remove it before adding the new path
            if ($pathElement) {
                $pathElement->remove();
            }
  
            (new DOMXPath($dom_common))->query("//*[@id='g1']")->item(0)->appendChild($newPath);//g1 is the group in which all paths are added
            Storage::put($CommonPath, $dom_common->saveXML());
          }
        }else{
            $original_path = Storage::get('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg');
            //It means it is the first path we add to area. We create the 2 files from the path

            //1. file without edits
          Storage::put($filePaths[0], $original_path);
          //2. file with edit
          $dom_common = new DOMDocument('1.0');
          $dom_common->preserveWhiteSpace = false;
          $dom_common->formatOutput = true;
          $dom_common->loadXML(simplexml_load_string($original_path)->asXML());
  
          // In order to use alpinejs in the svg, we must declare its prefix to avoid errors
          foreach ($dom_common->getElementsByTagName('svg') as $item) {
            $item->setAttribute("xmlns:x-bind", "https://alpinejs.dev");
            $item->setAttribute("xmlns:x-on", "https://alpinejs.dev");
            $item->setAttribute("class", "h-96"); // We set the height of the svg to better scale it with css
          }
  
          Storage::put($filePaths[1], $dom_common->saveXML());
        }
      }

    private function ProcessCommonPaths(){
          $route_id = $this->route->id;
          $filePath = 'paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector->id.'/edited/common_paths.svg';
          $dom_common = new DOMDocument('1.0');
          $dom_common->preserveWhiteSpace = false;
          $dom_common->formatOutput = true;
          $dom_common->loadXML(simplexml_load_string(Storage::get($filePath))->asXML());
  
          $xpath = new DOMXPath($dom_common);
          $item = $xpath->query("//*[@id='path_$route_id']")->item(0);
          $item->setAttribute("x-on:mouseover", "hightlightRoute($route_id)");
          $item->setAttribute("x-on:click", "selectRoute($route_id)");
          $item->setAttribute("x-bind:style", "(selectedRoute == $route_id || hightlightedRoute == $route_id) ? 'stroke-width :8;' : ''");
  
        Storage::put('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector->id.'/edited/common_paths.svg', $dom_common->saveXML());
    }
}

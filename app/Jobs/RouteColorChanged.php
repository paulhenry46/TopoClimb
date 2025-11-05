<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Route;
use App\Models\Site;
use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class RouteColorChanged implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Site $site, public Area $area, public Route $route)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      //Filter Image
      $path = 'photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id;
      $filtered_path = 'photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-filtered-'.$this->route->id;
      ImageFilter::dispatch($this->route->color, $path, $filtered_path);

      //Change Circle color
      $filePath = 'photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg';
      $id = $this->route->id;
      $xml = simplexml_load_string(Storage::get($filePath));
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());

      $xpath = new DOMXPath($dom);
      $item = $xpath->query("//*[@id='group_$id']")->item(0);
      $item->setAttribute("stroke", config('climb.colors')[$this->route->color]);
      Storage::put($filePath, $dom->saveXML());

      // Change line color (if applicable)
      if($this->area->type == 'trad'){
        //Edit commons paths source and generated
        $paths = ['paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->route->line->sector->id.'/common.src.svg',
                  'paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->route->line->sector->id.'/edited/common_paths.svg'];
        foreach($paths as $path){
          $filePath = $path;
          $id = $this->route->id;
          $xml = simplexml_load_string(Storage::get($filePath));
          $dom = new DOMDocument('1.0');
          $dom->preserveWhiteSpace = false;
          $dom->formatOutput = true;
          $dom->loadXML($xml->asXML());

          $xpath = new DOMXPath($dom);
          $item = $xpath->query("//*[@id='path_$id']")->item(0);
          $item->setAttribute("stroke", config('climb.colors')[$this->route->color]);
          Storage::put($filePath, $dom->saveXML());
        }
        $input = 'paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->route->line->sector->id.'/common.src.svg';
      $output = 'paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->route->line->sector->id.'/edited/android.svg';

      GenerateSectorPathForAndroid::dispatch($input, $output);
      }
    }
}

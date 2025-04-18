<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Route;
use App\Models\Site;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class ProcessCircleOfRoute implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Site $site, public Area $area, public Route $route, public string $path)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      $filePath = 'photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.svg';
      Storage::put('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.original.svg', $this->path);

      $input_file_path = Storage::path('photos/site-'.$this->site->id.'/area-'.$this->area->id.'/route-'.$this->route->id.'.original.svg');
      $output_file_path= storage_path('app/public/'.$filePath.'');
      
      $result = Process::run('inkscape --export-type=svg -o '.$output_file_path.' --export-area-drawing --export-plain-svg '.$input_file_path.'');
      $xml = simplexml_load_string(Storage::get($filePath));
      $dom = new DOMDocument('1.0');
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());

      $xpath = new DOMXPath($dom);
      $item = $xpath->query("//*[@id='area']")->item(0);
      $item->remove();

      Storage::put($filePath, $dom->saveXML());
    }
}

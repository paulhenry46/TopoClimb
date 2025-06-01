<?php

namespace App\Jobs;
use App\Models\Route;
use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class SoftDeleteRoute implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Route $route)
    {
        //
    }

    /**
     * Execute the job. Delete the route but keep its data
     */
    public function handle(): void
    {
        $grade =  $this->route->cotation;
        $line = $this->route->line;
        $sector = $line->sector;
        $area = $sector->area;
        $site = $area->site;
    
        #Delete path
        Storage::delete('paths/site-'.$site->id.'/area-'.$area->id.'/route-'.$this->route->id.'.original.svg');
        Storage::delete('paths/site-'.$site->id.'/area-'.$area->id.'/route-'.$this->route->id.'.svg');

        #Delete photo of route
        Storage::delete('photos/site-'.$site->id.'/area-'.$area->id.'/route-'.$this->route->id.'');
        Storage::delete('photos/site-'.$site->id.'/area-'.$area->id.'/route-'.$this->route->id.'-thumbnail');
        
        #Delete circle of route
        Storage::delete('photos/site-'.$site->id.'/area-'.$area->id.'/circle-'.$this->route->id.'.original.svg');
        Storage::delete('photos/site-'.$site->id.'/area-'.$area->id.'/circle-'.$this->route->id.'.svg');
        
        #Delete QR code
        Storage::delete('qrcode/site-'.$site->id.'/area-'.$area->id.'/route-'.$this->route->id.'.svg');

        #Remove path from common_path files
        if($sector->type == 'trad'){
            $filePaths = [
        'paths/site-'.$site->id.'/area-'.$area->id.'/sector-'.$sector->id.'/common.src.svg', 
        'paths/site-'.$site->id.'/area-'.$area->id.'/sector-'.$sector->id.'/edited/common_paths.svg'
          ];
  
          foreach ($filePaths as $CommonPath) {
            $dom_common = new DOMDocument('1.0');
            $dom_common->preserveWhiteSpace = false;
            $dom_common->formatOutput = true;
            $dom_common->loadXML(simplexml_load_string(Storage::get($CommonPath))->asXML());
  
            $pathElement = (new DOMXPath($dom_common))->query('//*[@id=\'path_'.$this->route->id.'\']')->item(0);
            if ($pathElement) {
                $pathElement->remove();
            }
            Storage::put($CommonPath, $dom_common->saveXML());
          }
        }

        $this->route->comment = 'softDeleted';
        $this->route->save();
    }
}

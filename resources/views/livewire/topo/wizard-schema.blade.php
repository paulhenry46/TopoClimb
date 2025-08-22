<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\Site;
use App\Models\Sector;
use App\Models\Line;
use App\Models\User;
use App\Models\Tag;
use App\Models\Route;
use Livewire\Attributes\Validate; 
use Illuminate\Support\Str;
use App\Jobs\ProcessMapForTopo;
new class extends Component {

    public Area $area;
    public Site $site;
    public $svg;
    public $url;
    public $routes;
    public $sector;

    public function mount(Site $site, Area $area, Sector $sector){

      $this->site = $site;
      $this->area = $area;
      $this->sector = $sector;
      $this->url = Storage::url('paths/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector->id.'/edited/common_paths.svg');
      $bg = Storage::url('plans/site-'.$this->site->id.'/area-'.$this->area->id.'/sector-'.$this->sector->id.'/schema');

     
     $this->routes = $this->sector->routes()->pluck('name','id')->toJson();
    }

    public function save(){
        ProcessMapForTopo::dispatchSync($this->site, $this->area, $this->svg, 'schema', $this->sector->id);
        $this->redirectRoute('admin.areas.topo.result.map.schema', ['site'=>$this->site, 'area'=>$this->area, 'sector'=> $this->sector], navigate:true);
    }

}; ?>

<div>
  <nav aria-label="Progress" class="p-4">
      <ol role="list" class="space-y-4 md:flex md:space-x-8 md:space-y-0">
          <li class="md:flex-1">
              <!-- Current Step --> <a class="flex flex-col border-l-4 border-gray-600 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4" aria-current="step"> <span class="text-sm font-medium text-gray-600">{{__('Step')}} 1</span> <span class="text-sm font-medium">{{__('Edit schema')}}</span> </a>
          </li>
          <li class="md:flex-1">
              <!-- Upcoming Step --> <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4"> <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 2</span> <span class="text-sm font-medium">{{__('Configure Topo')}}</span> </a>
          </li> @if($this->area->type == 'trad') <li class="md:flex-1">
              <!-- Upcoming Step --> <a class="group flex flex-col border-l-4 border-gray-200 py-2 pl-4 hover:border-gray-300 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4"> <span class="text-sm font-medium text-gray-500 group-hover:text-gray-700">{{__('Step')}} 3</span> <span class="text-sm font-medium">{{__('Save')}}</span> </a>
          </li> @endif
      </ol>

  </nav>
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Edit map')}}</h1>
          <p class="mt-2 text-sm text-gray-700"></p>
        </div>
      </div>
      <div class="rounded-md bg-gray-50 p-4 mb-3">
        <div class="flex">
          <div class="shrink-0">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
              <path d="m424-408-86-86q-11-11-28-11t-28 11q-11 11-11 28t11 28l114 114q12 12 28 12t28-12l226-226q11-11 11-28t-11-28q-11-11-28-11t-28 11L424-408Zm56 328q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-gray-800"> {{__('First, edit the schema')}}</h3>
            <div class="mt-2 text-sm text-gray-700">
              <ul role="list" class="list-disc space-y-1 pl-5">
                <li>{{__('The shift is the distance between the begining of the line and the begining of the text')}}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class='flex justify-between'>
        <div class='flex justify-center items-center space-x-2'>
           <div>
          {{ __('Shift')}} :
          </div>
           <x-button @click="$dispatch('s_plus')"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-407 324-252q-11 11-27.5 11.5T268-252q-11-11-11-28t11-28l155-155q23-23 57-23t57 23l155 155q11 11 11.5 27.5T692-252q-11 11-28 11t-28-11L480-407Zm0-240L324-492q-11 11-27.5 11.5T268-492q-11-11-11-28t11-28l155-155q23-23 57-23t57 23l155 155q11 11 11.5 27.5T692-492q-11 11-28 11t-28-11L480-647Z"/></svg></x-button>
            <x-button @click="$dispatch('s_minus')"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m480-313 156-155q11-11 27.5-11.5T692-468q11 11 11 28t-11 28L537-257q-23 23-57 23t-57-23L268-412q-11-11-11.5-27.5T268-468q11-11 28-11t28 11l156 155Zm0-240 156-155q11-11 27.5-11.5T692-708q11 11 11 28t-11 28L537-497q-23 23-57 23t-57-23L268-652q-11-11-11.5-27.5T268-708q11-11 28-11t28 11l156 155Z"/></svg></x-button>
        </div>
         <div class='flex justify-center items-center space-x-2'>
          <div>
          {{ __('Distance from line')}} :
          </div>
           <x-button @click="$dispatch('d_plus')"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-407 324-252q-11 11-27.5 11.5T268-252q-11-11-11-28t11-28l155-155q23-23 57-23t57 23l155 155q11 11 11.5 27.5T692-252q-11 11-28 11t-28-11L480-407Zm0-240L324-492q-11 11-27.5 11.5T268-492q-11-11-11-28t11-28l155-155q23-23 57-23t57 23l155 155q11 11 11.5 27.5T692-492q-11 11-28 11t-28-11L480-647Z"/></svg></x-button>
            <x-button @click="$dispatch('d_minus')"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m480-313 156-155q11-11 27.5-11.5T692-468q11 11 11 28t-11 28L537-257q-23 23-57 23t-57-23L268-412q-11-11-11.5-27.5T268-468q11-11 28-11t28 11l156 155Zm0-240 156-155q11-11 27.5-11.5T692-708q11 11 11 28t-11 28L537-497q-23 23-57 23t-57-23L268-652q-11-11-11.5-27.5T268-708q11-11 28-11t28 11l156 155Z"/></svg></x-button>
        </div>
        <div class='flex justify-center items-center space-x-2'>
           <div>
          {{ __('Font size')}} :
          </div>
           <x-button @click="$dispatch('f_plus')"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M480-407 324-252q-11 11-27.5 11.5T268-252q-11-11-11-28t11-28l155-155q23-23 57-23t57 23l155 155q11 11 11.5 27.5T692-252q-11 11-28 11t-28-11L480-407Zm0-240L324-492q-11 11-27.5 11.5T268-492q-11-11-11-28t11-28l155-155q23-23 57-23t57 23l155 155q11 11 11.5 27.5T692-492q-11 11-28 11t-28-11L480-647Z"/></svg></x-button>
            <x-button @click="$dispatch('f_minus')"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m480-313 156-155q11-11 27.5-11.5T692-468q11 11 11 28t-11 28L537-257q-23 23-57 23t-57-23L268-412q-11-11-11.5-27.5T268-468q11-11 28-11t28 11l156 155Zm0-240 156-155q11-11 27.5-11.5T692-708q11 11 11 28t-11 28L537-497q-23 23-57 23t-57-23L268-652q-11-11-11.5-27.5T268-708q11-11 28-11t28 11l156 155Z"/></svg></x-button>
        </div>
      </div>

              
              <div x-data="{message: ''}" @svg_sent.window="$wire.svg = $event.detail.message"
              @sent_to_wire.window="$wire.save()">
                <span x-text="message"></span>
        <script type="text/javascript" src="http://127.0.0.1:8000/dist/paper-full.js"></script>
        <script src='http://127.0.0.1:8000/dist/acorn.js'></script>
        
        <script type="text/paperscript" canvas="myCanvas">
            console.log(project.activeLayer.importSVG('{{ $this->url }}'));
            console.log(project.exportJSON());
            
            var shift = 30;
            var distance_from_line = 10;
            var fontsize = 18;
            const array_name ={!! $this->routes !!};


                var createAlignedText = function(str, path, style) {
                    if (str && str.length > 0 && path) {
                        // create PointText object for each glyph
                        var glyphTexts = [];
                        for (var i = 0; i < str.length; i++) {
                            glyphTexts[i] = createPointText(str.substring(i, i + 1), style);
                            glyphTexts[i].justification = "center";
                        }
                        // for each glyph find center xOffset
                        var xOffsets = [0];
                        for (var i = 1; i < str.length; i++) {
                            var pairText = createPointText(str.substring(i - 1, i + 1), style);
                            pairText.remove();
                            xOffsets[i] = xOffsets[i - 1] + pairText.bounds.width -
                                glyphTexts[i - 1].bounds.width / 2 - glyphTexts[i].bounds.width / 2;
                        }
                        // set point for each glyph and rotate glyph aorund the point
                        for (var i = 0; i < str.length; i++) {
                            var centerOffs = xOffsets[i];
                            if (path.length < centerOffs) {
                                if (path.closed) {
                                    centerOffs = centerOffs % path.length;
                                } else {
                                    centerOffs = undefined;
                                }
                            }
                            if (centerOffs === undefined) {
                                glyphTexts[i].remove();
                            } else {
                                var pathPoint = path.getPointAt(centerOffs + shift);
                                var normal = path.getNormalAt(centerOffs + shift) * 3;
                                glyphTexts[i].point = pathPoint;
                                var tan = path.getTangentAt(centerOffs + shift);
                                glyphTexts[i].rotate(tan.angle, pathPoint);
                            }
                        }
                        const numberOfCharacters = Array.from(str).length;
                        var middle = 0;
                        if (numberOfCharacters % 2 == 0) {
                            middle = numberOfCharacters / 2;
                        } else {
                            middle = (numberOfCharacters + 1) / 2;
                        }
                        offset = path.getOffsetOf(glyphTexts[middle].point);
                        var normal = path.getNormalAt(offset) * distance_from_line;
                        for (var i = 0; i < str.length; i++) {
                            glyphTexts[i].point = glyphTexts[i].point + normal;
                        }
                    }
                }

                  // create a PointText object for a string and a style
                  var createPointText = function(str, style) {
                      var text = new PointText();
                      text.content = str;
                      if (style) {
                          if (style.font) text.font = style.font;
                          if (style.fontFamily) text.fontFamily = style.fontFamily;
                          if (style.fontSize) text.fontSize = style.fontSize;
                          if (style.fontWieght) text.fontWeight = style.fontWeight;
                      }
                      return text;
                  }


              var removeText = function(){
                for (var item of project.getItems({class: PointText})) {
                              item.remove();
                }
              }

              var addText = function(){
                 for (var item of project.activeLayer.getItems({class: Path})) {
                  console.log('adding text on');
                  console.log(item);
                              createAlignedText(array_name[item.name.replace(/\D/g, "")], item, {
                  fontSize: fontsize
                });
                }
              }

              

          document.addEventListener('terminated', () => {
            console.log(project.exportJSON());
            addText();
            var evt = new CustomEvent('svg_sent', {
              detail: {
                  message: project.exportSVG({
                      asString: true
                  }),
              }
          });
          window.dispatchEvent(evt);
          
            var evt = new CustomEvent('sent_to_wire', {
                detail: {
                    message: 'ok',
                }
            });
            window.dispatchEvent(evt);
        })

        var apply = function(){
           removeText();
           addText();
        }

        document.addEventListener('s_plus', () => {
           shift++;
           apply();
        })
        document.addEventListener('s_minus', () => {
           shift--;
           apply();
        })

        document.addEventListener('d_plus', () => {
           distance_from_line++;
           apply();
        })
        document.addEventListener('d_minus', () => {
           distance_from_line--;
           apply();
        })

         document.addEventListener('f_plus', () => {
           fontsize++;
           apply();
        })
        document.addEventListener('f_minus', () => {
           fontsize--;
           apply();
        })

        addText()
        console.log("ok");
    </script>
        <canvas id="myCanvas" class="min-h-full min-w-full"></canvas>

      </div>
    </div>
    <div class="shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
      <div class="flex justify-end space-x-3">
        <x-secondary-button type="button">{{__('Cancel')}}</x-secondary-button>
        <x-button @click="$dispatch('terminated')">{{__('Continue')}}</x-button>
      </div>
    </div>
  </div>
</div>
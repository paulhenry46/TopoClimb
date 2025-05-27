<?php

use Livewire\Volt\Component;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

new class extends Component {

     public function createZipOfStorage(){
    $zip = new ZipArchive;
        $zipFileName = 'TopoClimb-files'.date("m.d.y").'.zip';
        if ($zip->open(public_path($zipFileName), ZipArchive::CREATE) === TRUE) {
            foreach (Storage::allFiles('public') as $file) {
                if (basename($file) !== '.gitignore') {
                    $zip->addFile(''.storage_path().'/app/'.$file.'', $file);
                }
            }
            $zip->close();
            return response()->download(public_path($zipFileName))->deleteFileAfterSend(true);
        }
} 

public function createBackupOfDB(){
    $path =''.storage_path().'/app/export.sql';
    if(env('DB_CONNECTION') == 'mysql'){
        exec('MYSQL_PWD="'.env('DB_PASSWORD').'" mysqldump -u '.env('DB_USERNAME').' '.env('DB_DATABASE').' > '.$path.' 2>&1');
    }
    return response()->download($path)->deleteFileAfterSend(true);
}
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Backup')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Create backup of topoClimb')}}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <x-button type='button' wire:click='createZipOfStorage()' >{{ __('Download files') }}</x-button>
            <x-button type='button'  wire:click='createBackupOfDB()'>{{ __('Export DB') }}</x-button>
        </div>
    </div>
</div>
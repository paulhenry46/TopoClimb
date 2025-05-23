<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

new class extends Component {


 


    public function mount(){
    }
    public function export()
    {
        // Fetch all users
        $users = User::all();

        // Define the CSV file name
        $fileName = 'users_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        // Open a file handle in memory
        $csvContent = fopen('php://temp', 'r+');

        // Add the CSV header
        fputcsv($csvContent, ['ID', 'Name', 'Email', 'Created At']);

        // Add user data to the CSV
        foreach ($users as $user) {
            fputcsv($csvContent, [$user->id, $user->name, $user->email, $user->created_at]);
        }

        // Rewind the file pointer
        rewind($csvContent);

        // Get the CSV content as a string
        $csvData = stream_get_contents($csvContent);

        // Close the file handle
        fclose($csvContent);

        // Return the file as a download response
        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }


}; ?>

<div>
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Export')}}</h1>
                <p class="mt-2 text-sm text-gray-700">{{__('You can export registered users in csv file')}}</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <x-button wire:click="export">{{ __('Export Users') }}</x-button>
            </div>
        </div>
        
    </div>



</div>
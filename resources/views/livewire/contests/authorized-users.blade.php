<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Validator;

new class extends Component {
    use WithFileUploads;
    
    public Contest $contest;
    
    #[Validate('required|exists:users,id')]
    public $user_id = '';

    public $search_user = '';
    
    #[Validate('nullable|file|mimes:csv,txt|max:2048')]
    public $csv_file;
    
    public $import_results = null;

    public function addAuthorizedUser()
    {
        $this->validate();


        // Check if already authorized
        if (($this->contest->isUserAuthorized(User::find($this->user_id))) && ($this->authorizedUsers()->count() !== 0)) {

            $this->dispatch('action_error', title: 'Already authorized', message: 'This user is already authorized for this contest!');
            return;
        }

        $user = User::find($this->user_id);
        $this->contest->addAuthorizedUser($user);

        $this->dispatch('action_ok', title: 'User authorized', message: 'User has been authorized successfully!');
        $this->reset(['user_id', 'search_user']);
    }

    public function removeAuthorizedUser($userId)
    {
        $user = User::find($userId);
        $this->contest->removeAuthorizedUser($user);
        
        $this->dispatch('action_ok', title: 'User removed', message: 'User has been removed from authorized list!');
    }
    
    public function clearAllAuthorizedUsers()
    {
        $this->contest->authorizedUsers()->detach();
        $this->dispatch('action_ok', title: 'All users removed', message: 'All authorized users have been removed!');
    }

    public function importCsv()
    {
        $this->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $this->csv_file->getRealPath();
        $file = fopen($path, 'r');
        
        // Read header row
        $header = fgetcsv($file);
        
        // Validate header
        if (!$header || count($header) < 2) {
            $this->dispatch('action_error', title: 'Invalid CSV', message: 'CSV must have at least 2 columns (name and email)!');
            fclose($file);
            return;
        }
        
        // Find name and email column indices (case-insensitive)
        $nameIndex = null;
        $emailIndex = null;
        
        foreach ($header as $index => $column) {
            $column = strtolower(trim($column));
            if ($column === 'name') {
                $nameIndex = $index;
            } elseif ($column === 'email') {
                $emailIndex = $index;
            }
        }
        
        if ($nameIndex === null || $emailIndex === null) {
            $this->dispatch('action_error', title: 'Invalid CSV', message: 'CSV must have "name" and "email" columns!');
            fclose($file);
            return;
        }
        
        $created = 0;
        $existing = 0;
        $authorized = 0;
        $errors = [];
        
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < max($nameIndex, $emailIndex) + 1) {
                continue;
            }
            
            $name = trim($row[$nameIndex]);
            $email = trim($row[$emailIndex]);
            
            if (empty($name) || empty($email)) {
                continue;
            }
            
            // Validate email
            $validator = Validator::make(['email' => $email], [
                'email' => 'required|email',
            ]);
            
            if ($validator->fails()) {
                $errors[] = "Invalid email: $email";
                continue;
            }
            
            // Check if user exists
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $existing++;
            } else {
                // Create new user with random password
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt(str()->random(16)),
                ]);
                $created++;
            }
            
            // Add to authorized users if not already
            if (!$this->contest->isUserAuthorized($user)) {
                $this->contest->addAuthorizedUser($user);
                $authorized++;
            }
        }
        
        fclose($file);
        
        $this->import_results = [
            'created' => $created,
            'existing' => $existing,
            'authorized' => $authorized,
            'errors' => $errors,
        ];
        
        $this->reset('csv_file');
        
        $message = "Import completed! Created: $created, Existing: $existing, Authorized: $authorized";
        $this->dispatch('action_ok', title: 'Import successful', message: $message);
    }

    #[Computed]
    public function authorizedUsers()
    {
        return $this->contest->authorizedUsers()->get();
    }

    #[Computed]
    public function searchUsers()
    {
        if (strlen($this->search_user) < 2) {
            return collect();
        }

        return User::where('name', 'like', '%' . $this->search_user . '%')
            ->orWhere('email', 'like', '%' . $this->search_user . '%')
            ->limit(10)
            ->get();
    }

    public function selectUser($userId)
    {
        $this->user_id = $userId;
        $user = User::find($userId);
        $this->search_user = $user->name;
    }
}; ?>

<div>
    <!-- CSV Import Section -->
    <div class="bg-white shadow sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{__('Bulk Import from CSV')}}</h3>
            <p class="text-sm text-gray-600 mb-4">{{__('Upload a CSV file with "name" and "email" columns. Users will be created if they don\'t exist and added to authorized users.')}}</p>
            
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <x-label for="csv_file">{{__('CSV File')}}</x-label>
                    <input 
                        type="file" 
                        id="csv_file"
                        wire:model="csv_file"
                        accept=".csv,.txt"
                        class="mt-1 block w-full file:inline-flex file:items-center file:px-4 file:py-2 file:bg-gray-800 file:border file:border-transparent file:rounded-md file:font-semibold file:text-sm file:text-white file:tracking-widest file:hover:bg-gray-700 file:focus:bg-gray-700 file:active:bg-gray-900 file:focus:outline-hidden file:disabled:opacity-50 file:transition file:ease-in-out file:duration-150"
                    />
                    <x-input-error for='csv_file'/>
                </div>
                
                <div>
                    <x-button wire:click="importCsv" wire:loading.attr="disabled">
                        {{__('Import')}}
                    </x-button>
                </div>
            </div>
            
            @if($import_results)
                <div class="mt-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">{{__('Import Results')}}</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <ul class="list-disc list-inside">
                                    <li>{{__('Users created')}}: {{ $import_results['created'] }}</li>
                                    <li>{{__('Existing users')}}: {{ $import_results['existing'] }}</li>
                                    <li>{{__('Users authorized')}}: {{ $import_results['authorized'] }}</li>
                                </ul>
                                @if(count($import_results['errors']) > 0)
                                    <div class="mt-2">
                                        <strong>{{__('Errors')}}:</strong>
                                        <ul class="list-disc list-inside">
                                            @foreach($import_results['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Manual Add User Section -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{__('Add Individual User')}}</h3>
            
            <div class="flex gap-4 items-end">
                <div class="flex-1 relative">
                    <x-label for="search_user">{{__('Search User')}}</x-label>
                    <input 
                        type="text" 
                        id="search_user"
                        wire:model.live="search_user"
                        placeholder="{{__('Search by name or email...')}}"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"
                    />
                    
                    @if($this->searchUsers->count() > 0 && $search_user && !$user_id)
                        <div class="mt-2 border rounded-md shadow-xl max-h-48 overflow-y-auto absolute z-10 bg-white w-full">
                            @foreach($this->searchUsers as $user)
                                <button 
                                    type="button"
                                    wire:click="selectUser({{ $user->id }})"
                                    class="block w-full text-left px-4 py-2 hover:bg-gray-100"
                                >
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <x-input-error for='user_id'/>
                </div>

                <div>
                    <x-button wire:click="addAuthorizedUser">
                        {{__('Add')}}
                    </x-button>
                </div>
            </div>
            <div class="flex items-center justify-between mb-4 mt-4">
                <h3 class="text-base font-semibold leading-6 text-gray-900">{{__('Authorized Users')}} ({{ $this->authorizedUsers->count() }})</h3>
                
                @if($this->authorizedUsers->count() > 0)
                    <button 
                        wire:click="clearAllAuthorizedUsers"
                        wire:confirm="{{__('Are you sure you want to remove all authorized users? This will allow all users to participate again.')}}"
                        class="text-red-600 hover:text-red-900 text-sm"
                    >
                        {{__('Clear All')}}
                    </button>
                @endif
            </div>
            
            @if($this->authorizedUsers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">{{__('Name')}}</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Email')}}</th>
                                <th class="relative py-3.5 pl-3 pr-4">
                                    <span class="sr-only">{{__('Actions')}}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($this->authorizedUsers as $user)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $user->email }}
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                        <button 
                                            wire:click="removeAuthorizedUser({{ $user->id }})"
                                            wire:confirm="{{__('Are you sure you want to remove this user from authorized list?')}}"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            {{__('Remove')}}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
            @if($this->authorizedUsers->count() === 0)
            <div class="mt-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">{{__('No restrictions')}}</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>{{__('Currently, all users can participate. Add authorized users to restrict access to this contest.')}}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
                @endif
        </div>
    </div>
</div>

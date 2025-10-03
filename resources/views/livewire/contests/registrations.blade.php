<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\User;
use App\Models\Route;
use App\Models\Log;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    
    #[Validate('required|exists:users,id')]
    public $user_id = '';
    
    #[Validate('required|exists:routes,id')]
    public $route_id = '';

    #[Validate('nullable|in:work,flash,view')]
    public $type = 'flash';

    #[Validate('nullable|in:top-rope,lead,bouldering')]
    public $way = 'bouldering';

    #[Validate('nullable|string')]
    public $comment = '';

    public $search_user = '';
    
    public $keep_route = false;

    public function registerClimb()
    {
        $this->validate();

        // Get the route to determine the grade
        $route = Route::findOrFail($this->route_id);

        // Create a log with verified_by set to current staff member
        Log::create([
            'route_id' => $this->route_id,
            'user_id' => $this->user_id,
            'grade' => $route->grade,
            'type' => $this->type,
            'way' => $this->way,
            'comment' => $this->comment,
            'verified_by' => auth()->id(),
        ]);

        $this->dispatch('action_ok', title: 'Climb registered', message: 'The climb has been registered successfully!');
        
        // Keep route if checkbox is checked, otherwise reset it
        if ($this->keep_route) {
            $this->reset(['user_id', 'search_user', 'comment', 'type', 'way']);
        } else {
            $this->reset(['user_id', 'route_id', 'search_user', 'comment', 'type', 'way']);
        }
        
        $this->type = 'flash';
        $this->way = 'bouldering';
    }

    #[Computed]
    public function contestRoutes()
    {
        return $this->contest->routes;
    }

    #[Computed]
    public function recentRegistrations()
    {
        return Log::whereIn('route_id', $this->contest->routes->pluck('id'))
            ->whereNotNull('verified_by')
            ->whereBetween('created_at', [$this->contest->start_date, $this->contest->end_date])
            ->with(['user', 'route', 'verifiedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
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

    public function deleteRegistration($id)
    {
        $log = Log::findOrFail($id);
        $log->delete();
        
        $this->dispatch('action_ok', title: 'Registration deleted', message: 'The registration has been deleted successfully!');
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    @if($contest->mode !== 'official')
        <div class="rounded-md bg-yellow-50 p-4 mb-6">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">{{ __('This contest is in Free Climb mode') }}</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ __('In Free Climb mode, users log their own climbs. This registration interface is only for Official contests.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="sm:flex sm:items-center mb-6">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Register Climbs')}}</h1>
                <p class="mt-2 text-sm text-gray-700">{{__('Register successful climbs for participants in this official contest. Logs will be marked as verified.')}}</p>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="bg-white shadow sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{__('New Registration')}}</h3>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="search_user" class="block text-sm font-medium leading-6 text-gray-900">{{__('Climber')}}</label>
                            
                            <!-- QR Scanner Button - Only visible on mobile -->
                            <button 
                                type="button"
                                @click="$dispatch('open-qr-scanner')"
                                class="md:hidden inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                </svg>
                                {{__('Scan QR')}}
                            </button>
                        </div>
                        
                        <input 
                            type="text" 
                            id="search_user"
                            wire:model.live="search_user"
                            placeholder="{{__('Search by name or email...')}}"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        />
                        
                        @if($this->searchUsers->count() > 0 && $search_user && !$user_id)
                            <div class="mt-2 border rounded-md shadow-sm max-h-48 overflow-y-auto">
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
                        
                        @error('user_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="route_id" class="block text-sm font-medium leading-6 text-gray-900">{{__('Route')}}</label>
                        <select 
                            id="route_id"
                            wire:model="route_id"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                            <option value="">{{__('Select a route')}}</option>
                            @foreach($this->contestRoutes as $route)
                                <option value="{{ $route->id }}">
                                    {{ $route->name }} - {{ $route->gradeFormated($route->line->sector->area->site->cotations_reverse()) ?? $route->defaultGradeFormated() }}
                                </option>
                            @endforeach
                        </select>
                        @error('route_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium leading-6 text-gray-900">{{__('Type')}}</label>
                        <select 
                            id="type"
                            wire:model="type"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                            <option value="flash">{{__('Flash')}}</option>
                            <option value="work">{{__('Work')}}</option>
                            <option value="view">{{__('View')}}</option>
                        </select>
                    </div>

                    <div>
                        <label for="way" class="block text-sm font-medium leading-6 text-gray-900">{{__('Way')}}</label>
                        <select 
                            id="way"
                            wire:model="way"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                            <option value="bouldering">{{__('Bouldering')}}</option>
                            <option value="top-rope">{{__('Top-rope')}}</option>
                            <option value="lead">{{__('Lead')}}</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="comment" class="block text-sm font-medium leading-6 text-gray-900">{{__('Comment (optional)')}}</label>
                        <textarea 
                            id="comment"
                            wire:model="comment"
                            rows="2"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        ></textarea>
                    </div>
                    
                    <div class="sm:col-span-2">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="keep_route"
                                wire:model="keep_route"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                            />
                            <label for="keep_route" class="ml-2 block text-sm text-gray-900">
                                {{__('Keep the selected route for next registration')}}
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{__('Useful when validating climbs for a specific route repeatedly')}}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <x-button wire:click="registerClimb">
                        {{__('Register Climb')}}
                    </x-button>
                </div>
            </div>
        </div>

        <!-- Recent Registrations -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">{{__('Recent Verified Registrations')}}</h3>
                
                @if($this->recentRegistrations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">{{__('Climber')}}</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Route')}}</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Type')}}</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Verified By')}}</th>
                                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Date')}}</th>
                                    <th class="relative py-3.5 pl-3 pr-4">
                                        <span class="sr-only">{{__('Actions')}}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($this->recentRegistrations as $log)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                            {{ $log->user->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $log->route->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ ucfirst($log->type) }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $log->verifiedBy?->name ?? __('Unknown') }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $log->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                            <button 
                                                wire:click="deleteRegistration({{ $log->id }})"
                                                wire:confirm="{{__('Are you sure you want to delete this registration?')}}"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                {{__('Delete')}}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">{{__('No verified registrations yet.')}}</p>
                @endif
            </div>
        </div>
    @endif
    
    <!-- QR Scanner Modal -->
    <div 
        x-data="{
            open: false,
            scanning: false,
            html5QrCode: null,
            error: '',
            init() {
                this.$watch('open', value => {
                    if (value) {
                        this.startScanner();
                    } else {
                        this.stopScanner();
                    }
                });
            },
            async startScanner() {
                this.error = '';
                this.scanning = true;
                
                try {
                    const qrCodeRegionId = 'qr-reader';
                    this.html5QrCode = new Html5Qrcode(qrCodeRegionId);
                    
                    await this.html5QrCode.start(
                        { facingMode: 'environment' },
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        async (decodedText, decodedResult) => {
                            try {
                                // Fetch user data from the scanned URL
                                const response = await fetch(decodedText);
                                if (response.ok) {
                                    const userData = await response.json();
                                    // Call Livewire method to select user
                                    @this.selectUser(userData.id);
                                    this.stopScanner();
                                    this.open = false;
                                } else {
                                    this.error = '{{ __('Invalid QR code. Please scan a valid user QR code.') }}';
                                }
                            } catch (error) {
                                this.error = '{{ __('Error reading QR code. Please try again.') }}';
                            }
                        },
                        (errorMessage) => {
                            // Scanner is working but no QR code detected yet
                        }
                    );
                } catch (err) {
                    this.error = '{{ __('Unable to access camera. Please allow camera access.') }}';
                    this.scanning = false;
                }
            },
            async stopScanner() {
                if (this.html5QrCode && this.scanning) {
                    try {
                        await this.html5QrCode.stop();
                        this.html5QrCode = null;
                        this.scanning = false;
                    } catch (err) {
                        console.error('Error stopping scanner:', err);
                    }
                }
            }
        }"
        @open-qr-scanner.window="open = true"
        x-show="open"
        x-cloak
        class="relative z-50"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        ></div>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div 
                    x-show="open"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                >
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{__('Scan User QR Code')}}</h3>
                            <button 
                                @click="open = false"
                                type="button"
                                class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-hidden"
                            >
                                <span class="sr-only">{{__('Close')}}</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">
                                {{__('Point your camera at the user\'s QR code to automatically select them.')}}
                            </p>
                            
                            <!-- QR Reader Container -->
                            <div id="qr-reader" class="w-full"></div>
                            
                            <!-- Error Message -->
                            <div x-show="error" class="mt-4 rounded-md bg-red-50 p-4">
                                <div class="flex">
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800" x-text="error"></h3>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Loading State -->
                            <div x-show="!scanning && !error" class="mt-4 text-center text-sm text-gray-500">
                                {{__('Initializing camera...')}}
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5 sm:mt-6">
                        <button
                            @click="open = false"
                            type="button"
                            class="inline-flex w-full justify-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                        >
                            {{__('Cancel')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

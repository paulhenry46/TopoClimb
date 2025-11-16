<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

new class extends Component {
    public Contest $contest;
    
    #[Validate('required|exists:users,id')]
    public $user_id = '';

    public $search_user = '';

    public function addStaff()
    {
        $this->validate();

        // Check if already a staff member
        if ($this->contest->isStaffMember(User::find($this->user_id))) {
            $this->dispatch('action_error', title: 'Already staff', message: 'This user is already a staff member!');
            return;
        }

        $user = User::find($this->user_id);
        $this->contest->addStaffMember($user);

        $this->dispatch('action_ok', title: 'Staff added', message: 'Staff member has been added successfully!');
        $this->reset(['user_id', 'search_user']);
    }

    public function removeStaff($userId)
    {
        $user = User::find($userId);
        $this->contest->removeStaffMember($user);
        
        $this->dispatch('action_ok', title: 'Staff removed', message: 'Staff member has been removed successfully!');
    }

    #[Computed]
    public function staffMembers()
    {
        return $this->contest->staffMembers()->get();
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

<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center mb-6">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Manage Staff Members')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Add or remove staff members who can register climber successes in official contests')}}</p>
        </div>
        <div class='flex gap-1'>
            <div class="grid grid-cols-1 gap-4">
                <div class='relative'>
                    <input 
                        type="text" 
                        id="search_user"
                        wire:model.live="search_user"
                        placeholder="{{__('Search by name or email...')}}"
                        class="mt-4 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-600 sm:text-sm sm:leading-6"
                    />
                    
                    @if($this->searchUsers->count() > 0 && $search_user && !$user_id)
                        <div class="mt-2 border rounded-md shadow-sm max-h-48 overflow-y-auto absolute">
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
            </div>

            <div class="mt-4">
                <x-button wire:click="addStaff">
                    {{__('Add')}}
                </x-button>
            </div>
        </div>
    </div>
    <!-- Current Staff Members -->
        <div class="px-4 py-5 sm:p-6">
            
            @if($this->staffMembers->count() > 0)
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
                            @foreach($this->staffMembers as $staff)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                        {{ $staff->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $staff->email }}
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                        <button 
                                            wire:click="removeStaff({{ $staff->id }})"
                                            wire:confirm="{{__('Are you sure you want to remove this staff member?')}}"
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
                <p class="text-sm text-gray-500">{{__('No staff members assigned yet.')}}</p>
            @endif
        </div>
</div>

<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\Site;
use App\Models\Route;
use App\Models\User;
use Livewire\Attributes\Validate; 
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public Site $site;
    public $modal_open = false;
    public $modal_title;
    public $modal_subtitle;
    public $modal_submit_message;

    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('nullable|string')]
    public $description = '';
    
    #[Validate('required|date')]
    public $start_date = '';
    
    #[Validate('required|date|after:start_date')]
    public $end_date = '';
    
    #[Validate('required|in:free,official')]
    public $mode = 'free';

    public $use_dynamic_points = false;
    public $team_mode = false;

    public $id_editing = 0;

    public function save()
    {
        $this->validate();

        if ($this->id_editing > 0) {
            $contest = Contest::findOrFail($this->id_editing);
            $contest->update([
                'name' => $this->name,
                'description' => $this->description,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'mode' => $this->mode,
                'use_dynamic_points' => $this->use_dynamic_points,
                'team_mode' => $this->team_mode,
            ]);
            $this->dispatch('action_ok', title: 'Contest updated', message: 'The contest has been updated successfully!');
        } else {
            Contest::create([
                'name' => $this->name,
                'description' => $this->description,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'mode' => $this->mode,
                'use_dynamic_points' => $this->use_dynamic_points,
                'team_mode' => $this->team_mode,
                'site_id' => $this->site->id,
            ]);
            $this->dispatch('action_ok', title: 'Contest created', message: 'The contest has been created successfully!');
        }

        $this->modal_open = false;
        $this->reset(['name', 'description', 'start_date', 'end_date', 'mode', 'use_dynamic_points', 'team_mode', 'id_editing']);
    }

    public function edit($id)
    {
        $contest = Contest::findOrFail($id);
        
        $this->id_editing = $contest->id;
        $this->name = $contest->name;
        $this->description = $contest->description;
        $this->start_date = $contest->start_date->format('Y-m-d\TH:i');
        $this->end_date = $contest->end_date->format('Y-m-d\TH:i');
        $this->mode = $contest->mode;
        $this->use_dynamic_points = $contest->use_dynamic_points;
        $this->team_mode = $contest->team_mode;
        
        $this->modal_title = __('Edit contest');
        $this->modal_subtitle = __('Update the contest information below.');
        $this->modal_submit_message = __('Update');
        $this->modal_open = true;
    }

    public function delete($id)
    {
        $contest = Contest::findOrFail($id);
        $contest->delete();
        
        $this->dispatch('action_ok', title: 'Contest deleted', message: 'The contest has been deleted successfully!');
        $this->render();
    }

    public function mount()
    {
        $this->modal_subtitle = __('Get started by filling in the information below to create a new contest.');
        $this->modal_title = __('New contest');
        $this->modal_submit_message = __('Create');
    }

    public function open_modal()
    {
        $this->reset(['name', 'description', 'start_date', 'end_date', 'mode', 'use_dynamic_points', 'team_mode', 'id_editing']);
        $this->modal_subtitle = __('Get started by filling in the information below to create a new contest.');
        $this->modal_title = __('New contest');
        $this->modal_submit_message = __('Create');
        $this->modal_open = true;
    }

    #[Computed]
    public function contests()
    {
        return Contest::where('site_id', $this->site->id)
            ->orderBy('start_date', 'desc')
            ->paginate(10);
    }
}; ?>

<div>
  <div class="px-4 sm:px-6 lg:px-8 py-8 bg-white">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Contests')}}</h1>
        <p class="mt-2 text-sm text-gray-700">{{__('Manage contests for this climbing site')}}</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <x-button wire:click="open_modal()" type="button">{{__('Create contest')}}</x-button>
      </div>
    </div>
    <div class="mt-8 flow-root">
  <div class="inline-block min-w-full align-middle">
    <div class="overflow-hidden rounded-lg ">
      <table class="min-w-full divide-y divide-gray-300 bg-white">
        <thead class="">
          <tr>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">{{__('Name')}}</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Start Date')}}</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('End Date')}}</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Status')}}</th>
            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
              <span class="sr-only">{{__('Edit')}}</span>
            </th>
          </tr>
        </thead>
        <tbody class="">
          @foreach ($this->contests as $contest)
          <tr class="hover:bg-gray-50 transition">
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
              <div class="">
                <div class='font-extrabold'>{{ $contest->name }}</div>
                <div class='text-sm'>
                  @if($contest->mode === 'free')
                <span class="text-xs opacity-50">
                  {{__('Free Climb')}}
                </span>
              @else
                <span class="text-xs opacity-50">
                   {{__('Official')}}
                </span>
              @endif

                </div>
              </div>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
              <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-xs font-medium text-gray-700">
                {{ $contest->start_date->format('Y-m-d H:i') }}
              </span>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
              <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-xs font-medium text-gray-700">
                {{ $contest->end_date->format('Y-m-d H:i') }}
              </span>
            </td>

            <td class="whitespace-nowrap px-3 py-4 text-sm">
              @if($contest->isActive())
                <span class="gap-1 inline-flex items-center rounded bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                  <x-icons.icon-check class="w-4 h-4 mr-1 text-green-500"/> {{__('Active')}}
                </span>
              @elseif($contest->isFuture())
                <span class="gap-1 inline-flex items-center rounded bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                  <x-icons.icon-calendar class="w-4 h-4 mr-1 text-gray-400"/> {{__('Upcoming')}}
                </span>
              @else
                <span class="gap-1 inline-flex items-center rounded bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                  <x-icons.icon-cancel class="w-4 h-4 mr-1 text-gray-400"/> {{__('Ended')}}
                </span>
              @endif
            </td>
            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
              <div class="flex gap-2 justify-end">
                <a href="{{ route('admin.contests.routes', ['site' => $site->id, 'contest' => $contest->id]) }}" class="text-gray-600 hover:text-gray-900" title="{{__('Routes')}}">
                 <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M760-120q-39 0-70-22.5T647-200H440q-66 0-113-47t-47-113q0-66 47-113t113-47h80q33 0 56.5-23.5T600-600q0-33-23.5-56.5T520-680H313q-13 35-43.5 57.5T200-600q-50 0-85-35t-35-85q0-50 35-85t85-35q39 0 69.5 22.5T313-760h207q66 0 113 47t47 113q0 66-47 113t-113 47h-80q-33 0-56.5 23.5T360-360q0 33 23.5 56.5T440-280h207q13-35 43.5-57.5T760-360q50 0 85 35t35 85q0 50-35 85t-85 35ZM200-680q17 0 28.5-11.5T240-720q0-17-11.5-28.5T200-760q-17 0-28.5 11.5T160-720q0 17 11.5 28.5T200-680Z"/></svg>
                </a>
                <a href="{{ route('admin.contests.steps', ['site' => $site->id, 'contest' => $contest->id]) }}" class="text-gray-600 hover:text-gray-900" title="{{__('Steps & Waves')}}">
                  <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M160-200v-80h640v80H160Zm0-240v-80h640v80H160Zm0-240v-80h640v80H160Z"/></svg>
                </a>
                @if($contest->team_mode)
                  <a href="{{ route('admin.contests.teams', ['site' => $site->id, 'contest' => $contest->id]) }}" class="text-gray-600 hover:text-gray-900" title="{{__('Teams')}}">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M40-160v-112q0-34 17.5-62.5T104-378q62-31 126-46.5T360-440q66 0 130 15.5T616-378q29 15 46.5 43.5T680-272v112H40Zm720 0v-120q0-44-24.5-84.5T666-434q51 6 96 20.5t84 35.5q36 20 55 44.5t19 53.5v120H760ZM360-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm400-160q0 66-47 113t-113 47q-11 0-28-2.5t-28-5.5q27-32 41.5-71t14.5-81q0-42-14.5-81T544-792q14-5 28-6.5t28-1.5q66 0 113 47t47 113Z"/></svg>
                  </a>
                @endif
                <a href="{{ route('admin.contests.categories', ['site' => $site->id, 'contest' => $contest->id]) }}" class="text-gray-600 hover:text-gray-900" title="{{__('Categories')}}">
                  <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="m260-520 220-360 220 360H260ZM700-80q-75 0-127.5-52.5T520-260q0-75 52.5-127.5T700-440q75 0 127.5 52.5T880-260q0 75-52.5 127.5T700-80Zm-580-20v-320h320v320H120Z"/></svg>
                </a>
                @if($contest->mode === 'official')
                  <a href="{{ route('admin.contests.staff', ['site' => $site->id, 'contest' => $contest->id]) }}" class="text-gray-600 hover:text-gray-900" title="{{__('Staff')}}">
                    <x-icons.icon-account-manager class="w-5 h-5"/>
                  </a>
                  <a href="{{ route('admin.contests.registrations', ['site' => $site->id, 'contest' => $contest->id]) }}" class="text-gray-600 hover:text-gray-900" title="{{__('Registrations')}}">
                    <x-icons.icon-task class="w-5 h-5"/>
                  </a>
                @endif
                <button wire:click="edit({{ $contest->id }})" class="text-gray-600 hover:text-gray-900" title="{{__('Edit')}}">
                  <x-icons.icon-edit class="w-5 h-5"/>
                </button>
                <button wire:click="delete({{ $contest->id }})" wire:confirm="{{__('Are you sure you want to delete this contest?')}}" class="text-red-600 hover:text-red-900" title="{{__('Delete')}}">
                  <x-icons.icon-delete class="w-5 h-5"/>
                </button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="mt-4">
        {{ $this->contests->links() }}
      </div>
    </div>
  </div>
</div>
  </div>

  <!-- Modal -->
{{-- Replace the modal with the drawer component for editing/creating contests --}}

  <x-drawer 
    open="modal_open" 
    :title="$modal_title" 
    :subtitle="$modal_subtitle"
    save_method_name="save"
    >
    <div>
        <div class="mt-4">
            <x-label for="name" value="{{ __('Name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-label for="description" value="{{ __('Description') }}" />
            <textarea id="description" class="border-gray-300 focus:border-gray-500 focus:ring-gray-500 rounded-md shadow-sm mt-1 block w-full" rows="3" wire:model="description"></textarea>
            <x-input-error for="description" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-label for="start_date" value="{{ __('Start Date') }}" />
            <x-input id="start_date" type="datetime-local" class="mt-1 block w-full" wire:model="start_date" />
            <x-input-error for="start_date" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-label for="end_date" value="{{ __('End Date') }}" />
            <x-input id="end_date" type="datetime-local" class="mt-1 block w-full" wire:model="end_date" />
            <x-input-error for="end_date" class="mt-2" />
        </div>

        <div class="mt-4">
           <x-label for="address" value="{{ __('Mode') }}" />
    <fieldset x-data="{ mode: $wire.entangle('mode') }">
        <legend class="sr-only">{{__('Contest Mode')}}</legend>
        <div class="-space-y-px bg-white">
            <label :class="mode == 'free' ? 'z-10 border-gray-200 bg-gray-50' : 'border-gray-200'" class="rounded-t-md relative flex cursor-pointer border p-4 focus:outline-none">
                <input x-model="mode" type="radio" name="contest-mode" value="free"
                    class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-gray-600 border-gray-300 focus:ring-0 focus:ring-offset-0 active:ring-0 active:ring-gray-600"
                    aria-labelledby="contest-mode-free-label" aria-describedby="contest-mode-free-description"/>
                <span class="ml-3 flex flex-col">
                    <span :class="mode == 'free' ? 'text-gray-900' : 'text-gray-900'" id="contest-mode-free-label" class="block text-sm font-medium">{{__('Free Climb')}}</span>
                    <span :class="mode == 'free' ? 'text-gray-700' : 'text-gray-500'" id="contest-mode-free-description" class="block text-sm">
                        {{__('Users log routes normally.')}}
                    </span>
                </span>
            </label>
            <label :class="mode == 'official' ? 'z-10 border-gray-200 bg-gray-50' : 'border-gray-200'" class="rounded-b-md relative flex cursor-pointer border p-4 focus:outline-none">
                <input x-model="mode" type="radio" name="contest-mode" value="official"
                    class="mt-0.5 h-4 w-4 shrink-0 cursor-pointer text-gray-600 border-gray-300 focus:ring-0 focus:ring-offset-0 active:ring-0 active:ring-gray-600"
                    aria-labelledby="contest-mode-official-label" aria-describedby="contest-mode-official-description"/>
                <span class="ml-3 flex flex-col">
                    <span :class="mode == 'official' ? 'text-gray-900' : 'text-gray-900'" id="contest-mode-official-label" class="block text-sm font-medium">{{__('Official')}}</span>
                    <span :class="mode == 'official' ? 'text-gray-700' : 'text-gray-500'" id="contest-mode-official-description" class="block text-sm">
                        {{__('Staff members register climber successes.')}}
                    </span>
                </span>
            </label>
        </div>
    </fieldset>
    <x-input-error for="mode" class="mt-2" />
</div>

        <div class="mt-4">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="use_dynamic_points" type="checkbox" wire:model="use_dynamic_points" 
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="ml-3 text-sm">
                    <label for="use_dynamic_points" class="font-medium text-gray-700">{{ __('Use Dynamic Points Calculation') }}</label>
                    <p class="text-gray-500">{{ __('Points are divided by the number of climbers who completed the route.') }}</p>
                </div>
            </div>
            <x-input-error for="use_dynamic_points" class="mt-2" />
        </div>

        <div class="mt-4">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="team_mode" type="checkbox" wire:model="team_mode" 
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="ml-3 text-sm">
                    <label for="team_mode" class="font-medium text-gray-700">{{ __('Enable Team Mode') }}</label>
                    <p class="text-gray-500">{{ __('Users can create and join teams, and rankings are calculated by team.') }}</p>
                </div>
            </div>
            <x-input-error for="team_mode" class="mt-2" />
        </div>

    </div>
    <x-slot name="footer">
        <x-secondary-button wire:click="$set('modal_open', false)">
            {{ __('Cancel') }}
        </x-secondary-button>
        <x-button class="ml-2">
            {{ $modal_submit_message }}
        </x-button>
    </x-slot>
</x-drawer>
</div>

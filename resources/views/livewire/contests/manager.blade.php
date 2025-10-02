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
            ]);
            $this->dispatch('action_ok', title: 'Contest updated', message: 'The contest has been updated successfully!');
        } else {
            Contest::create([
                'name' => $this->name,
                'description' => $this->description,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'mode' => $this->mode,
                'site_id' => $this->site->id,
            ]);
            $this->dispatch('action_ok', title: 'Contest created', message: 'The contest has been created successfully!');
        }

        $this->modal_open = false;
        $this->reset(['name', 'description', 'start_date', 'end_date', 'mode', 'id_editing']);
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
        $this->reset(['name', 'description', 'start_date', 'end_date', 'mode', 'id_editing']);
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
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Contests')}}</h1>
        <p class="mt-2 text-sm text-gray-700">{{__('Manage contests for this climbing site')}}</p>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
        <x-button wire:click="open_modal()" type="button">{{__('Add contest')}}</x-button>
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <table class="min-w-full divide-y divide-gray-300">
            <thead>
              <tr>
                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">{{__('Name')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Start Date')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('End Date')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Mode')}}</th>
                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{__('Status')}}</th>
                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                  <span class="sr-only">{{__('Edit')}}</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              @foreach ($this->contests as $contest)
              <tr>
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ $contest->name }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $contest->start_date->format('Y-m-d H:i') }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $contest->end_date->format('Y-m-d H:i') }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  @if($contest->mode === 'free')
                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{__('Free Climb')}}</span>
                  @else
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">{{__('Official')}}</span>
                  @endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  @if($contest->isActive())
                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{__('Active')}}</span>
                  @elseif($contest->isFuture())
                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">{{__('Upcoming')}}</span>
                  @else
                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">{{__('Ended')}}</span>
                  @endif
                </td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                  <a href="{{ route('admin.contests.routes', ['site' => $site->id, 'contest' => $contest->id]) }}" class="text-blue-600 hover:text-blue-900 mr-4">{{__('Routes')}}</a>
                  @if($contest->mode === 'official')
                    <a href="{{ route('admin.contests.registrations', ['site' => $site->id, 'contest' => $contest->id]) }}" class="text-blue-600 hover:text-blue-900 mr-4">{{__('Registrations')}}</a>
                  @endif
                  <button wire:click="edit({{ $contest->id }})" class="text-gray-600 hover:text-gray-900 mr-4">{{__('Edit')}}</button>
                  <button wire:click="delete({{ $contest->id }})" wire:confirm="{{__('Are you sure you want to delete this contest?')}}" class="text-red-600 hover:text-red-900">{{__('Delete')}}</button>
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
  <x-dialog-modal wire:model="modal_open">
    <x-slot name="title">
      {{ $modal_title }}
    </x-slot>

    <x-slot name="content">
      <p class="text-sm text-gray-600 mb-4">{{ $modal_subtitle }}</p>

      <div class="mt-4">
        <x-label for="name" value="{{ __('Name') }}" />
        <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
        <x-input-error for="name" class="mt-2" />
      </div>

      <div class="mt-4">
        <x-label for="description" value="{{ __('Description') }}" />
        <textarea id="description" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" rows="3" wire:model="description"></textarea>
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
        <x-label for="mode" value="{{ __('Mode') }}" />
        <select id="mode" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" wire:model="mode">
          <option value="free">{{__('Free Climb')}}</option>
          <option value="official">{{__('Official')}}</option>
        </select>
        <x-input-error for="mode" class="mt-2" />
        <p class="mt-1 text-xs text-gray-500">
          {{__('Free Climb: Users log routes normally. Official: Staff members register climber successes.')}}
        </p>
      </div>
    </x-slot>

    <x-slot name="footer">
      <x-secondary-button wire:click="$set('modal_open', false)">
        {{ __('Cancel') }}
      </x-secondary-button>

      <x-button class="ml-2" wire:click="save">
        {{ $modal_submit_message }}
      </x-button>
    </x-slot>
  </x-dialog-modal>
</div>

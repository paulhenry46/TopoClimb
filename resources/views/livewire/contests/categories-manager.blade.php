<?php

use Livewire\Volt\Component;
use App\Models\Contest;
use App\Models\ContestCategory;
use Livewire\Attributes\Validate;

new class extends Component {
    public Contest $contest;
    public $modal_open = false;
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('nullable|string')]
    public $type = '';
    
    #[Validate('nullable|string')]
    public $criteria = '';
    
    public $id_editing = 0;

    public function save()
    {
        $this->validate();

        if ($this->id_editing > 0) {
            $category = ContestCategory::findOrFail($this->id_editing);
            $category->update([
                'name' => $this->name,
                'type' => $this->type,
                'criteria' => $this->criteria,
            ]);
            $this->dispatch('action_ok', title: 'Category updated', message: 'Category has been updated successfully!');
        } else {
            ContestCategory::create([
                'name' => $this->name,
                'type' => $this->type,
                'criteria' => $this->criteria,
                'contest_id' => $this->contest->id,
            ]);
            $this->dispatch('action_ok', title: 'Category created', message: 'Category has been created successfully!');
        }

        $this->modal_open = false;
        $this->reset(['name', 'type', 'criteria', 'id_editing']);
    }

    public function edit($id)
    {
        $category = ContestCategory::findOrFail($id);
        $this->id_editing = $category->id;
        $this->name = $category->name;
        $this->type = $category->type;
        $this->criteria = $category->criteria;
        $this->modal_open = true;
    }

    public function delete($id)
    {
        $category = ContestCategory::findOrFail($id);
        $category->delete();
        $this->dispatch('action_ok', title: 'Category deleted', message: 'Category has been deleted successfully!');
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center mb-6">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Manage Categories')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Create categories to organize contest rankings by age, gender, or custom criteria')}}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <button wire:click="$set('modal_open', true)" type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{__('Create Category')}}
            </button>
        </div>
    </div>

    <!-- Categories List -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if($contest->categories->count() > 0)
                <div class="space-y-3">
                    @foreach($contest->categories as $category)
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $category->name }}</h3>
                                    @if($category->type)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($category->type) }}
                                        </span>
                                    @endif
                                </div>
                                @if($category->criteria)
                                    <p class="text-sm text-gray-600 mt-1">{{ $category->criteria }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">{{ $category->users->count() }} {{__('participants')}}</p>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $category->id }})" class="text-gray-600 hover:text-gray-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $category->id }})" 
                                    wire:confirm="{{__('Are you sure you want to delete this category?')}}"
                                    class="text-red-600 hover:text-red-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    {{__('No categories created yet. Click "Create Category" to get started.')}}
                </div>
            @endif
        </div>
    </div>

    <!-- Create/Edit Category Modal -->
    <x-drawer 
        open="modal_open" 
        :title="$id_editing > 0 ? __('Edit Category') : __('Create Category')" 
        :subtitle="$id_editing > 0 ? __('Update category information') : __('Create a new category for this contest')"
        save_method_name="save">
        <div>
            <div class="mt-4">
                <x-label for="name" value="{{ __('Category Name') }}" />
                <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" 
                    placeholder="{{ __('e.g., Men 18-25, Women Elite, Youth') }}" />
                <x-input-error for="name" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-label for="type" value="{{ __('Type') }}" />
                <select id="type" wire:model="type" 
                    class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full">
                    <option value="">{{ __('Select type (optional)') }}</option>
                    <option value="age">{{ __('Age') }}</option>
                    <option value="gender">{{ __('Gender') }}</option>
                    <option value="custom">{{ __('Custom') }}</option>
                </select>
                <x-input-error for="type" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-label for="criteria" value="{{ __('Criteria (optional)') }}" />
                <x-input id="criteria" type="text" class="mt-1 block w-full" wire:model="criteria" 
                    placeholder="{{ __('e.g., 18-25, Male, Female, etc.') }}" />
                <x-input-error for="criteria" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Additional information about this category') }}</p>
            </div>
        </div>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('modal_open', false)">
                {{ __('Cancel') }}
            </x-secondary-button>
            <x-button class="ml-2">
                {{ $id_editing > 0 ? __('Update') : __('Create') }}
            </x-button>
        </x-slot>
    </x-drawer>
</div>

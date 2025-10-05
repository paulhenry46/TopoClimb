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
    
    public $auto_assign = false;
    
    #[Validate('nullable|integer|min:0')]
    public $min_age = null;
    
    #[Validate('nullable|integer|min:0')]
    public $max_age = null;
    
    #[Validate('nullable|string')]
    public $gender = '';
    
    public $id_editing = 0;

    public function updated($property)
    {
        // Reset form when modal is closed
        if ($property === 'modal_open' && $this->modal_open === false && $this->id_editing > 0) {
            $this->reset(['name', 'type', 'criteria', 'auto_assign', 'min_age', 'max_age', 'gender', 'id_editing']);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'criteria' => $this->criteria,
            'auto_assign' => $this->auto_assign,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'gender' => $this->gender,
        ];

        if ($this->id_editing > 0) {
            $category = ContestCategory::findOrFail($this->id_editing);
            $category->update($data);
            $this->dispatch('action_ok', title: 'Category updated', message: 'Category has been updated successfully!');
        } else {
            $data['contest_id'] = $this->contest->id;
            ContestCategory::create($data);
            $this->dispatch('action_ok', title: 'Category created', message: 'Category has been created successfully!');
        }

        $this->modal_open = false;
        $this->reset(['name', 'type', 'criteria', 'auto_assign', 'min_age', 'max_age', 'gender', 'id_editing']);
    }

    public function edit($id)
    {
        $category = ContestCategory::findOrFail($id);
        $this->id_editing = $category->id;
        $this->name = $category->name;
        $this->type = $category->type;
        $this->criteria = $category->criteria;
        $this->auto_assign = $category->auto_assign;
        $this->min_age = $category->min_age;
        $this->max_age = $category->max_age;
        $this->gender = $category->gender;
        $this->modal_open = true;
    }

    public function delete($id)
    {
        $category = ContestCategory::findOrFail($id);
        $category->delete();
        $this->dispatch('action_ok', title: 'Category deleted', message: 'Category has been deleted successfully!');
    }

    public function cancel()
    {
        $this->modal_open = false;
        $this->reset(['name', 'type', 'criteria', 'auto_assign', 'min_age', 'max_age', 'gender', 'id_editing']);
    }

    public function open_modal()
    {
        $this->reset(['name', 'type', 'criteria', 'auto_assign', 'min_age', 'max_age', 'gender', 'id_editing']);
        $this->modal_open = true;
    }
}; ?>

<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center mb-6">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">{{__('Manage Categories')}}</h1>
            <p class="mt-2 text-sm text-gray-700">{{__('Create categories to organize contest rankings by age, gender, or custom criteria')}}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <x-button wire:click="open_modal" type="button">
                {{__('Create Category')}}
            </x-button>
        </div>
    </div>

    <!-- Categories List -->
    <div class="">
        <div class="px-4 py-5 sm:p-6">
            @if($contest->categories->count() > 0)
                <div class="gap-2 grid grid-cols-3">
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
                                    @if($category->auto_assign)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('Auto-assign') }}
                                        </span>
                                    @endif
                                </div>
                                @if($category->criteria)
                                    <p class="text-sm text-gray-600 mt-1">{{ $category->criteria }}</p>
                                @endif
                                @if($category->min_age || $category->max_age)
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ __('Age') }}: 
                                        @if($category->min_age && $category->max_age)
                                            {{ $category->min_age }}-{{ $category->max_age }}
                                        @elseif($category->min_age)
                                            {{ $category->min_age }}+
                                        @else
                                            &lt; {{ $category->max_age }}
                                        @endif
                                    </p>
                                @endif
                                @if($category->gender)
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ __('Gender') }}: {{ ucfirst($category->gender) }}
                                    </p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">{{ $category->users->count() }} {{__('participants')}}</p>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $category->id }})" class="text-gray-600 hover:text-gray-900">
                                    <x-icons.icon-edit/>
                                </button>
                                <button wire:click="delete({{ $category->id }})" 
                                    wire:confirm="{{__('Are you sure you want to delete this category?')}}"
                                    class="text-red-700 hover:text-red-900">
                                    <x-icons.icon-delete/>
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
        <div x-data="{ autoAssign: $wire.entangle('auto_assign') }">
            <div class="mt-4">
                <x-label for="name" value="{{ __('Category Name') }}" />
                <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" 
                    placeholder="{{ __('e.g., Men 18-25, Women Elite, Youth') }}" />
                <x-input-error for="name" class="mt-2" />
            </div>

            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" wire:model.live="auto_assign" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Automatically assign users to this category') }}</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">{{ __('Users will be automatically added to this category when they participate in the contest if they match the criteria') }}</p>
            </div>

            <div class="mt-4" x-show="!autoAssign">
                <x-label for="criteria" value="{{ __('Criteria (optional)') }}" />
                <x-input id="criteria" type="text" class="mt-1 block w-full" wire:model="criteria" 
                    placeholder="{{ __('e.g., Elite climbers, Beginners') }}" />
                <x-input-error for="criteria" class="mt-2" />
            </div>

            <div class="mt-4" x-show="autoAssign">
                <x-label for="gender" value="{{ __('Gender Filter') }}" />
                <select id="gender" wire:model="gender" 
                    class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1 block w-full">
                    <option value="">{{ __('All genders (no filter)') }}</option>
                    <option value="male">{{ __('Male') }}</option>
                    <option value="female">{{ __('Female') }}</option>
                    <option value="other">{{ __('Other') }}</option>
                </select>
                <x-input-error for="gender" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Select a specific gender or leave as "All genders" to not filter by gender') }}</p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4" x-show="autoAssign">
                <div>
                    <x-label for="min_age" value="{{ __('Minimum Age') }}" />
                    <x-input id="min_age" type="number" min="0" class="mt-1 block w-full" wire:model="min_age" 
                        placeholder="{{ __('e.g., 18') }}" />
                    <x-input-error for="min_age" class="mt-2" />
                </div>
                <div>
                    <x-label for="max_age" value="{{ __('Maximum Age') }}" />
                    <x-input id="max_age" type="number" min="0" class="mt-1 block w-full" wire:model="max_age" 
                        placeholder="{{ __('e.g., 25') }}" />
                    <x-input-error for="max_age" class="mt-2" />
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-end space-x-3">
            <x-secondary-button wire:click="cancel">
                {{ __('Cancel') }}
            </x-secondary-button>
            <x-button class="ml-2">
                {{ $id_editing > 0 ? __('Update') : __('Create') }}
            </x-button>
        </div>
        </x-slot>
    </x-drawer>
</div>

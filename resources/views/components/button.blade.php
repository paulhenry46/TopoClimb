<button {{ $attributes->merge(['type' => 'submit', 'class' => 'cursor-pointer inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-hidden disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

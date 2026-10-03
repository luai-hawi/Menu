<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('messages.dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if ($ownerWithoutRestaurants ?? false)
                    <div class="p-6 text-center text-gray-900 dark:text-gray-100" data-owner-empty-state>
                        <i class="fas fa-store text-4xl text-gray-400 mb-4" aria-hidden="true"></i>
                        <h3 class="text-lg font-semibold mb-2">{{ __('admin.owner_empty.title') }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ __('admin.owner_empty.body') }}</p>
                    </div>
                @else
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        {{ __('messages.you_are_logged_in') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

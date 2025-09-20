<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('messages.create_your_restaurant') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card">
                <div class="text-center mb-8">
                    <div class="text-6xl mb-4">🏪</div>
                    <h3 class="text-2xl font-bold text-white mb-2">{{ __('messages.setup_your_restaurant') }}</h3>
                    <p class="text-gray-400">{{ __('messages.fill_details_restaurant') }}</p>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="alert alert-error mb-6">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('restaurant.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-6">
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('messages.restaurant_name') }} *</label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name') }}"
                                   placeholder="{{ __('messages.enter_restaurant_name') }}"
                                   class="w-full" required>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">{{ __('messages.restaurant_description') }}</label>
                            <textarea id="description" name="description" rows="4"
                                      placeholder="{{ __('messages.describe_restaurant_optional') }}"
                                      class="w-full">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="logo" class="form-label">{{ __('messages.restaurant_logo') }}</label>
                            <input type="file" id="logo" name="logo" accept="image/*" class="w-full">
                            <p class="text-gray-400 text-sm mt-2">{{ __('messages.upload_logo_instructions') }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" class="btn btn-success flex-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                {{ __('messages.create_restaurant_btn') }}
                            </button>
                            <a href="{{ route('restaurant.dashboard') }}" class="btn bg-gray-600 hover:bg-gray-700 text-white text-center">
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
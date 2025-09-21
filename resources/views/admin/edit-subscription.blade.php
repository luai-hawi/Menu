<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.index') }}" class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-white leading-tight">
                    Edit Subscription Cost
                </h2>
                <p class="text-gray-400 mt-1">Update subscription cost for {{ $subscription->user->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
                <form action="{{ route('admin.subscription.update', $subscription) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="amount" class="block text-sm font-medium text-white mb-2">
                            Subscription Cost ($)
                        </label>
                        <input type="number"
                               id="amount"
                               name="amount"
                               value="{{ old('amount', $subscription->amount) }}"
                               step="0.01"
                               min="0"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-500 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 transition-colors">
                            Update Cost
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
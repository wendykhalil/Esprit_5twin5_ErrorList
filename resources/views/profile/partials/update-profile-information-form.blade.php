<section>
    <header class="mb-6">
        <h2 class="text-2xl font-semibold text-green-900" style="font-family: Fraunces, Georgia, serif">
            Informations Personnelles
        </h2>
        <p class="text-gray-600 mt-2">
            Mettez à jour vos informations de profil.
        </p>
    </header>

    @if (session('status') === 'profile-updated')
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700 font-medium">
                ✓ Profil mis à jour avec succès.
            </p>
        </div>
    @endif

    @if (session('status') === 'photo-deleted')
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700 font-medium">
                ✓ Photo supprimée avec succès.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <!-- Profile Photo Section -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Photo de Profil</h3>
            
            <div class="flex items-center gap-6">
                <!-- Photo Preview -->
                <div class="shrink-0">
                    @if ($user->profile_photo)
                        <img 
                            src="{{ $user->getProfilePhotoUrl() }}" 
                            alt="{{ $user->name }}"
                            class="w-24 h-24 rounded-full object-cover border-2 border-green-200"
                        />
                    @else
                        <div class="w-24 h-24 rounded-full bg-green-600 flex items-center justify-center border-2 border-green-200">
                            <span class="text-2xl font-bold text-white">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Upload Section -->
                <div class="flex-1">
                    <label for="profile_photo" class="block text-sm font-medium text-gray-900 mb-2">
                        Choisir une photo
                    </label>
                    <input 
                        type="file"
                        id="profile_photo"
                        name="profile_photo"
                        accept="image/jpeg,image/jpg,image/png,image/webp"
                        class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg p-2 cursor-pointer"
                    />
                    @error('profile_photo')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, WebP jusqu'à 2 Mo</p>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-900 mb-2">
                    Nom *
                </label>
                <input 
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent"
                    required
                />
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-900 mb-2">
                    Email *
                </label>
                <input 
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent"
                    required
                />
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-900 mb-2">
                    Téléphone
                </label>
                <input 
                    type="tel"
                    id="phone"
                    name="phone"
                    value="{{ old('phone', $user->phone) }}"
                    placeholder="+216 XX XXX XXX"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent"
                />
                @error('phone')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-sm font-medium text-gray-900 mb-2">
                    Adresse
                </label>
                <input 
                    type="text"
                    id="address"
                    name="address"
                    value="{{ old('address', $user->address) }}"
                    placeholder="123 Rue de la Paix"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent"
                />
                @error('address')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- City -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-900 mb-2">
                    Ville
                </label>
                <input 
                    type="text"
                    id="city"
                    name="city"
                    value="{{ old('city', $user->city) }}"
                    placeholder="Tunis"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent"
                />
                @error('city')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Country -->
            <div>
                <label for="country" class="block text-sm font-medium text-gray-900 mb-2">
                    Pays
                </label>
                <input 
                    type="text"
                    id="country"
                    name="country"
                    value="{{ old('country', $user->country) }}"
                    placeholder="Tunisie"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent"
                />
                @error('country')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Bio -->
        <div class="mb-8">
            <label for="bio" class="block text-sm font-medium text-gray-900 mb-2">
                Bio
            </label>
            <textarea 
                id="bio"
                name="bio"
                rows="4"
                placeholder="Dites-nous en plus sur vous..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent"
            >{{ old('bio', $user->bio) }}</textarea>
            @error('bio')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Maximum 1000 caractères</p>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center gap-4">
            <button 
                type="submit"
                class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
            >
                Enregistrer
            </button>
        </div>
    </form>

    <!-- Delete Photo Form (Outside main form) -->
    @if ($user->profile_photo)
        <div class="mt-4">
            <form method="POST" action="{{ route('profile.photo.delete') }}" class="inline">
                @csrf
                @method('DELETE')
                <button 
                    type="submit"
                    class="text-sm text-red-600 hover:text-red-700 font-medium"
                >
                    Supprimer la photo
                </button>
            </form>
        </div>
    @endif
</section>

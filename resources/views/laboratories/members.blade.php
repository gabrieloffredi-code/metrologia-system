<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gerenciar Membros de:') }} {{ $laboratory->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">{{ session('error') }}</div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">{{ __('Adicionar Novo Membro') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">Adicione um novo membro usando o e-mail registrado dele no sistema.</p>
                </header>

                <form method="POST" action="{{ route('laboratories.addMember', $laboratory) }}" class="mt-6 space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('E-mail do Usuário')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Função')" />
                        <select id="role" name="role" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" required>
                            <option value="VISUALIZADOR">Visualizador</option>
                            <option value="EDITOR">Editor</option>
                            <option value="ADM">Administrador</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Adicionar Membro') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Membros Atuais') }}</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Função</th>
                                <th class="px-6 py-3 bg-gray-50">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($members as $member)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $member->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $member->email }}</td>
                                    
                                    {{-- Coluna da Função (EDITÁVEL) --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <form action="{{ route('laboratories.updateMemberRole', ['laboratory' => $laboratory, 'membership' => $member->pivot->id]) }}" method="POST" class="flex items-center space-x-2">
                                            @csrf
                                            @method('PUT') 

                                            <select name="role" onchange="this.form.submit()" 
                                                    class="border-gray-300 rounded-md text-sm py-1 w-36 {{ $member->pivot->role == 'ADM' ? 'bg-indigo-100' : '' }}"
                                                    {{-- O ADM não pode editar a própria função para evitar lock-out (se for o único) --}}
                                                    {{ $member->id == Auth::id() && $member->pivot->role == 'ADM' ? 'disabled' : '' }}> 
                                                <option value="VISUALIZADOR" {{ $member->pivot->role == 'VISUALIZADOR' ? 'selected' : '' }}>Visualizador</option>
                                                <option value="EDITOR" {{ $member->pivot->role == 'EDITOR' ? 'selected' : '' }}>Editor</option>
                                                <option value="ADM" {{ $member->pivot->role == 'ADM' ? 'selected' : '' }}>Administrador</option>
                                            </select>
                                        </form>
                                    </td>
                                    
                                    {{-- Coluna da Ação (Remoção) --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if ($member->id != Auth::id())
                                            <form action="{{ route('laboratories.removeMember', ['laboratory' => $laboratory, 'membership' => $member->pivot->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este membro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Remover</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">Você</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
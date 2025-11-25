@forelse ($users as $user)
    <div class="user-item list-group-item list-group-item-action" 
         data-id="{{ $user->id }}" 
         data-name="{{ $user->fullname }}"
         data-type="{{ $user->user_type }}"> <div class="d-flex justify-content-between">
            <strong>{{ $user->fullname }}</strong>
            {{-- Label kecil di kanan untuk memastikan --}}
            <small class="text-muted badge bg-light text-dark">{{ strtoupper($user->user_type) }}</small> 
        </div>
        <small class="text-muted" style="font-size: 0.8rem;">
            ID: {{ $user->nis ?? ($user->nik ?? '-') }}
        </small>
    </div>
@empty
    <div class="p-2 text-center text-muted">Tidak ditemukan.</div>
@endforelse
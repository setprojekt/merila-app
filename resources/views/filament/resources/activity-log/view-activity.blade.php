<div class="space-y-4">
    <div>
        <h3 class="text-lg font-semibold">Osnovni Podatki</h3>
        <dl class="mt-2 space-y-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Tip:</dt>
                <dd class="text-sm text-gray-900">{{ $record->log_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Opis:</dt>
                <dd class="text-sm text-gray-900">{{ $record->description }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Dogodek:</dt>
                <dd class="text-sm text-gray-900">{{ $record->event }}</dd>
            </div>
            @if($record->causer)
            <div>
                <dt class="text-sm font-medium text-gray-500">Uporabnik:</dt>
                <dd class="text-sm text-gray-900">{{ $record->causer->full_name }} ({{ $record->causer->email }})</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500">Datum in čas:</dt>
                <dd class="text-sm text-gray-900">{{ $record->created_at->format('d.m.Y H:i:s') }}</dd>
            </div>
        </dl>
    </div>

    @if($record->properties && $record->properties->count() > 0)
    <div>
        <h3 class="text-lg font-semibold">Podatki</h3>
        
        @if($record->properties->has('attributes'))
        <div class="mt-2">
            <h4 class="text-sm font-medium text-gray-700">Novi podatki:</h4>
            <pre class="mt-1 text-xs bg-gray-100 p-2 rounded overflow-x-auto">{{ json_encode($record->properties->get('attributes'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif

        @if($record->properties->has('old'))
        <div class="mt-2">
            <h4 class="text-sm font-medium text-gray-700">Stari podatki:</h4>
            <pre class="mt-1 text-xs bg-gray-100 p-2 rounded overflow-x-auto">{{ json_encode($record->properties->get('old'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif
    </div>
    @endif

    @if($record->subject)
    <div>
        <h3 class="text-lg font-semibold">Povezan Objekt</h3>
        <dl class="mt-2 space-y-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Model:</dt>
                <dd class="text-sm text-gray-900">{{ class_basename($record->subject_type) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">ID:</dt>
                <dd class="text-sm text-gray-900">{{ $record->subject_id }}</dd>
            </div>
        </dl>
    </div>
    @endif
</div>

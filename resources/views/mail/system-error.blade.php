<x-mail::message>
# {{ $error->occurrences > 1 ? 'Repeated' : 'New' }} error: {{ class_basename($error->exception_class) }}

**Occurrences:** {{ $error->occurrences }}
**First seen:** {{ $error->first_seen_at->format('d/m/Y H:i') }}
**Last seen:** {{ $error->last_seen_at->format('d/m/Y H:i') }}

**Message:** {{ $error->message }}

**Location:** {{ $error->file }}:{{ $error->line }}

@if($error->url)
**URL:** {{ $error->url }} ({{ $error->http_method }})
@endif

@if($analysis)
## AI Analysis

{{ $analysis }}
@endif

<x-mail::panel>
{{ $error->trace }}
</x-mail::panel>
</x-mail::message>

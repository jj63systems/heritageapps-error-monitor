<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: -apple-system, sans-serif; color: #1e293b; margin: 0; padding: 24px; background: #f5f3ef;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 24px; border: 1px solid #ebe8e3;">
        <h1 style="font-size: 18px; margin: 0 0 16px;">
            {{ $error->occurrences > 1 ? 'Repeated' : 'New' }} error: {{ class_basename($error->exception_class) }}
        </h1>

        <table style="font-size: 13px; margin-bottom: 16px;">
            <tr><td style="color: #64748b; padding-right: 12px;">Occurrences</td><td>{{ $error->occurrences }}</td></tr>
            <tr><td style="color: #64748b; padding-right: 12px;">First seen</td><td>{{ $error->first_seen_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td style="color: #64748b; padding-right: 12px;">Last seen</td><td>{{ $error->last_seen_at->format('d/m/Y H:i') }}</td></tr>
            @if($error->url)
                <tr><td style="color: #64748b; padding-right: 12px;">URL</td><td>{{ $error->http_method }} {{ $error->url }}</td></tr>
            @endif
        </table>

        <p style="font-size: 14px;"><strong>Message:</strong> {{ $error->message }}</p>
        <p style="font-size: 14px;"><strong>Location:</strong> {{ $error->file }}:{{ $error->line }}</p>

        @if($analysis)
            <h2 style="font-size: 15px; margin: 20px 0 8px;">AI Analysis</h2>
            <p style="font-size: 14px; white-space: pre-line;">{{ $analysis }}</p>
        @endif

        <h2 style="font-size: 15px; margin: 20px 0 8px;">Stack Trace</h2>
        <pre style="background: #f5f3ef; border-radius: 8px; padding: 12px; font-size: 11px; overflow-x: auto; white-space: pre-wrap;">{{ $error->trace }}</pre>
    </div>
</body>
</html>

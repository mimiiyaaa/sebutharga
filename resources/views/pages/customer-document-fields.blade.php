<dl class="space-y-5 text-sm">
    @foreach($fields as $key => $label)
        <div><dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line break-words font-medium text-gray-800 dark:text-gray-200">{{ filled($document[$key] ?? null) ? $document[$key] : '—' }}</dd></div>
    @endforeach
</dl>

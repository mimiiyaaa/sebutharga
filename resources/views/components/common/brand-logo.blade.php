@props(['transparent' => false])
<span {{ $attributes->class(['relative inline-block shrink-0 overflow-hidden rounded-lg align-middle', 'bg-transparent dark:bg-transparent' => $transparent, 'bg-white dark:bg-white' => !$transparent]) }}>
    <img src="{{ asset($transparent ? 'images/logo/k-one-resources-transparent.png' : 'images/logo/k-one-resources.png') . '?v=20261119' }}" alt="K ONE Resources"
        width="1280" height="866"
        class="block h-full w-full object-contain" />
</span>

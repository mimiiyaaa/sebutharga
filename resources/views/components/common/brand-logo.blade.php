@props(['transparent' => false])
<span {{ $attributes->class(['relative inline-block shrink-0 overflow-hidden rounded-lg align-middle', 'bg-transparent dark:bg-transparent' => $transparent, 'bg-white dark:bg-white' => !$transparent]) }}>
    <img src="{{ asset($transparent ? 'images/logo/k1r-transparent.png' : 'images/logo/k1r-resources.png') }}" alt="K1R Resources"
        width="2000" height="2000"
        class="block h-full w-full object-contain" />
</span>

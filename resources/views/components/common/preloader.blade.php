<div
  x-show="loaded"
  x-cloak
  x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 650)})"
  class="fixed inset-0 z-999999 flex h-screen w-screen items-center justify-center bg-white/95 dark:bg-gray-950/95"
  role="status"
  aria-label="Memuatkan"
>
  <div class="flex flex-col items-center gap-4">
    <div class="relative flex h-56 w-56 items-center justify-center">
      <div class="absolute bottom-2 h-8 w-36 rounded-full bg-brand-500/20 blur-xl dark:bg-brand-400/20"></div>
      <img src="{{ asset('images/loading-mascot-transparent.png') }}" alt="" class="relative h-56 w-56 object-contain drop-shadow-xl animate-[loader-wiggle_2.4s_ease-in-out_infinite]">
    </div>
    <div class="flex items-center gap-1.5" aria-hidden="true">
      <span class="h-2 w-2 animate-bounce rounded-full bg-brand-500 [animation-delay:-0.3s]"></span>
      <span class="h-2 w-2 animate-bounce rounded-full bg-brand-500 [animation-delay:-0.15s]"></span>
      <span class="h-2 w-2 animate-bounce rounded-full bg-brand-500"></span>
    </div>
  </div>
</div>

<div class="md:relative md:grid md:grid-cols-[1fr_2.5rem_80rem_2.5rem_1fr] md:grid-rows-[1fr_1px_auto_1px_1fr]">

  <div class="md:col-start-3 md:row-start-3">
      {{ $slot }}
      <x-grid-pattern-hr/>
  </div>
  <div class="relative md:-right-px md:col-start-2 md:row-span-full md:row-start-1 md:border-x md:border-x-(--pattern-fg) md:bg-[image:repeating-linear-gradient(315deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] md:bg-[size:10px_10px] md:bg-fixed"></div>
  <div class="relative md:-left-px md:col-start-4 md:row-span-full md:row-start-1 md:border-x md:border-x-(--pattern-fg) md:bg-[image:repeating-linear-gradient(315deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] md:bg-[size:10px_10px] md:bg-fixed"></div>
  <div class="relative md:-bottom-px md:col-span-full md:col-start-1 md:row-start-2 md:h-px md:bg-gray-200"></div>
  <div class="relative md:-top-px md:col-span-full md:col-start-1 md:row-start-4 md:h-px md:bg-gray-200"></div>
</div>
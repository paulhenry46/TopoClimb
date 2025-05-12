<div class="relative grid  grid-cols-[1fr_2.5rem_80rem_2.5rem_1fr] grid-rows-[1fr_1px_auto_1px_1fr]  ">
    <div class="col-start-3 row-start-3 " >
        {{ $slot }}
      </div>
      <div class="relative -right-px col-start-2 row-span-full row-start-1 border-x border-x-(--pattern-fg) bg-[image:repeating-linear-gradient(315deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] bg-[size:10px_10px] bg-fixed"></div>
      <div class="relative -left-px col-start-4 row-span-full row-start-1 border-x border-x-(--pattern-fg) bg-[image:repeating-linear-gradient(315deg,_var(--pattern-fg)_0,_var(--pattern-fg)_1px,_transparent_0,_transparent_50%)] bg-[size:10px_10px] bg-fixed"></div>
      <div class="relative -bottom-px col-span-full col-start-1 row-start-2 h-px bg-gray-200"></div>
      <div class="relative -top-px col-span-full col-start-1 row-start-4 h-px bg-gray-200"></div>
    </div>
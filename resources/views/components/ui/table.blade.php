<div {{ $attributes->twMerge('w-full sm:relative sm:overflow-x-auto sm:rounded-md sm:border sm:border-border p-4') }}>
    <table class="w-full caption-bottom text-sm block sm:table sm:min-w-120 sm:border">
        {{ $slot }}
    </table>
</div>

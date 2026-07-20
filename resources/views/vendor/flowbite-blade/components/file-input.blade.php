@if($dropzone)
    <div class="flex items-center justify-center w-full">
        @if($label)
            <label for="{{ $id }}" class="flex flex-col items-center justify-center w-full h-64 border-2 border-default-medium border-dashed rounded-base cursor-pointer bg-neutral-secondary-soft hover:bg-neutral-secondary-medium">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <x-fwb-o-cloud-arrow-up class="w-8 h-8 mb-4 text-body" />
                    <p class="mb-2 text-sm text-body"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                    @if($helper)
                        <p class="text-xs text-body">{{ $helper }}</p>
                    @endif
                </div>
                <input id="{{ $id }}" type="file" class="hidden" {{ $multiple ? 'multiple' : '' }} {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes }} />
            </label>
        @endif
    </div>
@else
    <div>
        @if($label)
            <label class="block mb-2 {{ $sizeClasses }} font-medium text-heading" for="{{ $id }}">{{ $label }}</label>
        @endif
        <input class="block w-full {{ $sizeClasses }} text-heading border border-default-medium rounded-base cursor-pointer bg-neutral-secondary-soft focus:outline-none" id="{{ $id }}" type="file" {{ $multiple ? 'multiple' : '' }} {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} {{ $attributes }}>
        @if($helper)
            <p class="mt-1 text-sm text-body">{{ $helper }}</p>
        @endif
    </div>
@endif

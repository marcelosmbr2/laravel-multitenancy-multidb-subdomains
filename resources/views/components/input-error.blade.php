@props(['for'])

@error($for)
    <p class="label text-error">{{ $message }}</p>
@enderror

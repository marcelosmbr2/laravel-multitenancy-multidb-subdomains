@if (session('status'))
    <div role="alert" {{ $attributes->class(['alert alert-success']) }}>
        <span>{{ session('status') }}</span>
    </div>
@endif

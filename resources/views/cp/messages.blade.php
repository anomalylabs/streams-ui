<!-- messages.blade.php -->
@foreach (Messages::pull() as $message)

@switch($message['type'])
    @case('success')
        @php $color = 'green'; @endphp
        @break
    @case('error')
        @php $color = 'red'; @endphp
        @break
    @case('info')
        @php $color = 'blue'; @endphp
        @break
    @default
        
@endswitch

<div class="flex items-center bg-{{ $color }} text-white text-sm font-bold px-4 py-3" role="alert">  
    <p>{{ $message['content'] }}</p>
</div>
@endforeach

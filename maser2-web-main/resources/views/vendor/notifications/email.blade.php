@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level == 'error')
# Whoops!
@else
# Hello!
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{!! $line !!}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
$color = match ($level) {
    'success' => 'green',
    'error' => 'red',
    default => 'blue',
};
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{!! $line !!}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
Equipe Maser Log<br>http://www.masertransportes.com.br/
@endif

{{-- Subcopy --}}
@isset($actionText)
@component('mail::subcopy')
Se você tiver problemas para clicar no "{{ $actionText }}" Botão, copie e cole o URL abaixo
no seu navegador da web: [{{ $actionUrl }}]({{ $actionUrl }})
@endcomponent
@endisset
@endcomponent
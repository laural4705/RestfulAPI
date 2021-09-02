@component('mail::message')
    # Hello {{$user->name}}

    Thank you for creating an account. Please verify your e-mail address using the button below:

    @component('mail::button', ['url' => route('verify',$user->verification_token)])
        Verify Account
    @endcomponent

    Thanks,
    {{ config('app.name') }}
@endcomponent

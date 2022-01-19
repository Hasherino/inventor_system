@component('mail::message')
# Set Password

Click the button to set your password.

@component('mail::button', ['url' => 'https://egle-na.github.io/inventorizavimas/create-password?token='.$token.'&email='.$email])
Set password
@endcomponent

@endcomponent

@component('mail::message')
# Set Password

Click the button to set your password.

@component('mail::button', ['url' => 'http://localhost:8000/change-password?token='.$token.'&email='.$email])
Set password
@endcomponent

@endcomponent

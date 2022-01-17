@component('mail::message')
# Set Password

Click the button to set your password.

@component('mail::button', ['url' => 'https://inventor-system.herokuapp.com/api/new-password?token='.$token.'&email='.$email])
Set password
@endcomponent

@endcomponent

<x-mail::message>
    # We have successfully received your request

    Hello {{ $data['name'] }}, We have successfully received your request! One of our representative will contact you
    shortly through email or phone.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>

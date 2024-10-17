<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>Pending Record Retention Request</title>
    </head>

    <body>
        {{-- TODO: add vcc branding to emails --}}
        {{-- TODO: make sure this message is okay by Todd --}}
        <p>Dear {{ $data['authorizer_name'] }},</br></p>
        <p>A record retention request has been submitted by {{ $data['requestor_name'] }} (<a href="mailto:{{ $data['requestor_email'] }}">{{ $data['requestor_email'] }}</a>).</br></p>
        <p>Login to <a href="{{ getenv('AUTHORIZER_SITE') }}">{{ getenv('AUTHORIZER_SITE') }}</a> to modify and authorize this request.</p>
    </body>
</html>



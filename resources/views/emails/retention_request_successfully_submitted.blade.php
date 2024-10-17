<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>Retention Request Successfully Submitted</title>
    </head>

    <body>
        {{-- TODO: add vcc branding to emails --}}
        {{-- TODO: make sure this message is okay by Todd --}}
        <p>Dear {{ $data['name'] }},</br></p>
        <p>You're request to have your records retained has been submitted to the VCC Records department. A records employee will approve your request.</br></p>
        <p>Please check your email for confirmation of that approval and instructions on the next steps you must complete.</p>
    </body>
</html>


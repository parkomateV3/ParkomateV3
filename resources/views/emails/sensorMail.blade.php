<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert mail</title>
</head>

<body style="font-family: Arial, sans-serif; color: #000000;">
    <h3>{{ $mailData['subject'] }}</h3>
    <p>
        @if($mailData['device'] == 'sensor')
        <strong>Site Name:</strong> {{ $mailData['site_name'] }}<br>
        <strong>Floor Name:</strong> {{ $mailData['floor_name'] }}<br>
        <strong>Zonal Name:</strong> {{ $mailData['zonal_name'] }}<br>
        <strong>Sensor Name:</strong> {{ $mailData['sensor_name'] }}<br>
        <strong>Sensor Unique No:</strong> {{ $mailData['sensor_unique_no'] }}<br>
        <strong>Status:</strong> {{ $mailData['status'] }}<br>
        <strong>Date:</strong> {{ $mailData['date'] }}
        @elseif($mailData['device'] == 'display')
        <strong>Display Unique No:</strong> {{ $mailData['sensor_id'] }}<br>
        <strong>Message:</strong> Unregistered device is communicating with server.<br>
        @elseif($mailData['device'] == 'displayapi')
        <strong>Display Unique No:</strong> {{ $mailData['sensor_id'] }}<br>
        <strong>Message:</strong> This display api not calling more than 15 minutes.<br>
        @elseif($mailData['device'] == 'site_error')
        <strong>Site Name:</strong> {{ $mailData['site_name'] }}<br>
        <strong>Message:</strong> The site is offline.<br>
        @elseif($mailData['device'] == 'deviceerror')
        <strong>Device Unique No:</strong> {{ $mailData['sensor_id'] }}<br>
        <strong>Message:</strong> Unregistered device is communicating with server.<br>
        @else
        <strong>Zonal Unique No:</strong> {{ $mailData['sensor_id'] }}<br>
        <strong>Message:</strong> Unregistered device is communicating with server.<br>
        @endif
    </p>
</body>

</html>
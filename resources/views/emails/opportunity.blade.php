<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opportunity Email</title>
</head>
<body>
    {!! $messageContent !!}
    <img src="{{ $trackingPixelUrl }}" alt="" style="border:0; width:100px; height:100px;">
    <br>
    <a href="{{ $trackableLinkUrl }}">Click here</a>
</body>
</html>


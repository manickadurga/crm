<!DOCTYPE html>
<html lang="en" class="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation</title>
</head>
<body>
    <p>You have been invited to our platform.</p>
    <p>Contact Name: {{ $client->contact_name }}</p>
    <p>Primary Phone: {{ $client->primary_phone }}</p>
    <p>Email: {{ $client->email }}</p>
    <p>Thank you for using our application!</p>
</body>
</html>

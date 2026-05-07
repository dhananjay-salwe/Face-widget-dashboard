<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forbidden</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-md mx-auto mt-20 bg-white shadow rounded p-6 text-center">
        <h1 class="text-xl font-semibold mb-2">Forbidden</h1>
        <p class="text-gray-600">This widget is not authorized for the requesting domain: {{ $host ?? 'unknown' }}</p>
    </div>
</body>
</html>

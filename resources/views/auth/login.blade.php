<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in • Tuition App</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-indigo-50">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
      <div class="bg-white/80 backdrop-blur rounded-2xl shadow-xl border p-6">
        <h1 class="text-2xl font-bold mb-1 text-center">Welcome back</h1>
        <p class="text-sm text-center text-gray-600 mb-6">Sign in to continue to <b>Tuition App</b></p>
        <form method="POST" action="/login" class="space-y-4">@csrf
          <div>
            <label class="text-sm text-gray-700">Email</label>
            <input name="email" class="mt-1 border rounded w-full p-2" value="admin@example.com">
          </div>
          <div>
            <label class="text-sm text-gray-700">Password</label>
            <input type="password" name="password" class="mt-1 border rounded w-full p-2" value="password">
          </div>
          <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded">Sign in</button>
          @error('email')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </form>
        <div class="text-xs text-center mt-4 text-gray-500">
          Tip: change your password after login from <b>Account → Change Password</b>.
        </div>
      </div>
    </div>
  </div>
</body>
</html>

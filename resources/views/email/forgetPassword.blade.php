<p>Hi <strong>{{$name}}</strong>,</p>

<p>We received a request to reset your Nutriflow password.</p>

<p>If you use Google Sign-In, you can log in without a password here:<br> [Sign in with Google]</p>

<p>If you’d prefer to reset your password and use email login, follow this link:<br>

<a href="{{ route('reset.password.get', $token) }}">Set Password</a></p>

<p><strong>Note:</strong> This link expires in 24 hours. If you didn’t request a reset, you can safely ignore this email.</p>

<p>Thanks,<br> Batchbase Support Team </p>
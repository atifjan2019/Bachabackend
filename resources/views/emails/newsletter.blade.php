<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject ?? 'Newsletter' }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f9f9f9; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background: #1a1a1a; padding: 20px; text-align: center; }
        .header img { max-height: 50px; }
        .content { padding: 30px; }
        .footer { background: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #777; }
        .footer a { color: #d32f2f; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Using a fallback text or logo -->
            <h2 style="color: white; margin: 0; text-transform: uppercase; letter-spacing: 2px;">Bacha Stylo</h2>
        </div>
        
        <div class="content">
            {!! $bodyContent !!}
        </div>

        <div class="footer">
            <p>You are receiving this email because you subscribed to our newsletter.</p>
            <p>
                <a href="{{ url('/') }}">Visit our store</a> | 
                <!-- This unsubscribe link can be wired up later to remove the subscriber -->
                <a href="{{ url('/newsletter/unsubscribe?email=' . urlencode($email)) }}">Unsubscribe</a>
            </p>
            <p>&copy; {{ date('Y') }} Bacha Stylo. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

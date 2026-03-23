<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>{{ $title ?? 'Page' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #f4f7fb;
      font-family: 'Segoe UI', sans-serif;
    }

    .header {
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      color: #fff;
      padding: 50px 0;
      text-align: center;
    }

    .content-box {
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      margin-top: -40px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    }

    .content-box h2 {
      color: #4e73df;
      margin-top: 20px;
    }

    .content-box p {
      color: #555;
      line-height: 1.7;
    }
  </style>
</head>

<body>

  <!-- Header -->
  <div class="header">
    <h1>{{ $title }}</h1>
    <p>Read carefully before using our services</p>
  </div>

  <!-- Content -->
  <div class="container">
    <div class="content-box">

      @if (!empty($content))
        {!! $content !!}
      @else
        <p>No content available.</p>
      @endif

    </div>
  </div>

</body>

</html>

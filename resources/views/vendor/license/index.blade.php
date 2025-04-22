<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Renewal</title>
    <!-- Add Bootstrap 5 CDN link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Your framework license has expired!</strong>
                <p>The usage license has expired.</p>
                <p>Please renew your license to continue using the service.</p>
                <a target="_blank" href="https://themeforest.net/" class="btn btn-success">Renew now</a>
                <a target="_blank" href="https://zalo.me/0336216546" class="btn btn-primary">Contact support</a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <strong>Renewal Instructions</strong>
            </div>
            <div class="card-body">
                <p>To renew your license, please follow these steps:</p>
                <ol>
                    <li>Log in to your account.</li>
                    <li>Go to the license renewal page.</li>
                    <li>Select a payment method and complete the transaction.</li>
                </ol>
            </div>
        </div>

        <div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong>Return to the homepage if your renewal was successful</strong><br><br>

                <a href="{{ config('app.url') }}" class="btn btn-success">Back to homepage</a>
            </div>
        </div>

    </div>

    <!-- Add Bootstrap 5 JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

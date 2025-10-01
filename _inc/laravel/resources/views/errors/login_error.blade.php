<!DOCTYPE html>
<html>
<head>
    <title>{{ !empty($title) ? $title : "Error" }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">{{ !empty($title) ? $title : "Error" }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            {{!empty($message) ? $message : "Undefined error." }}
                        </div>
                        <a href="{{ route('login') }}" class="btn btn-primary">Try Again</a>
                        <a href="{{ url('/') }}" class="btn btn-secondary">Go Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
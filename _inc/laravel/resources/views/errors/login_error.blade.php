<!DOCTYPE html>
<html>
<head>
    <title>{{ !empty($title) ? $title : "Error" }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row {{ VC::JCC }}">
            <div class="{{ VC::CM6 }}">
                <div class="card">
                    <div class="{{ VC::CD_HD }} bg-danger {{ VC::TXT_WT }}">
                        <h4 class="{{ VC::MB0 }}">{{ !empty($title) ? $title : "Error" }}</h4>
                    </div>
                    <div class="{{ VC::CD_BD }}">
                        <div class="{{ VC::ALT_DNG }}">
                            {{!empty($message) ? $message : "Undefined error." }}
                        </div>
                        <a href="{{ route('login') }}" class="{{ VC::BT_PRM }}">Try Again</a>
                        <a href="{{ url('/') }}" class="btn btn-secondary">Go Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

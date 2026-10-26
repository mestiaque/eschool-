<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal – Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a237e 0%, #283593 40%, #0d47a1 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .portal-card { background: rgba(255,255,255,0.97); border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 420px; width: 100%; padding: 2.5rem 2rem; }
        .portal-logo { width: 72px; height: 72px; background: linear-gradient(135deg,#1565c0,#0d47a1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .portal-logo i { font-size: 32px; color: #fff; }
        .pin-input { letter-spacing: 8px; font-size: 1.4rem; font-weight: 700; text-align: center; }
        .btn-portal { background: linear-gradient(135deg,#1565c0,#0d47a1); color: #fff; border: none; border-radius: 8px; padding: .65rem 2rem; font-size: 1rem; font-weight: 600; width: 100%; }
        .btn-portal:hover { opacity: .9; color: #fff; }
    </style>
</head>
<body>
<div class="portal-card">
    <div class="portal-logo"><i class="fas fa-graduation-cap"></i></div>
    <h4 class="text-center fw-bold text-primary mb-0">{{ sm_t('Student Portal') }}</h4>
    <p class="text-center text-muted small mb-4">{{ sm_t('Login with your Student ID and PIN') }}</p>

    @if(session('error'))
    <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('portal.login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">{{ sm_t('Student ID') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror"
                       placeholder="{{ sm_t('e.g. STU-2026-0001') }}" value="{{ old('student_id') }}" required autofocus>
            </div>
            @error('student_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">{{ sm_t('Portal PIN') }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="portal_pin" class="form-control pin-input @error('portal_pin') is-invalid @enderror"
                       placeholder="••••" maxlength="10" required>
            </div>
            @error('portal_pin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-portal"><i class="fas fa-sign-in-alt me-2"></i>{{ sm_t('Login') }}</button>
    </form>

    <p class="text-center text-muted small mt-3 mb-0">{{ sm_t('Contact your school admin for PIN setup.') }}</p>
</div>
</body>
</html>

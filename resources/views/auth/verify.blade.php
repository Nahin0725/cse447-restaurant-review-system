@extends('layouts.auth')

@section('content')
    <div style="margin-bottom: 20px; text-align: right;">
        <a href="/login" class="button secondary" style="font-size: 14px; padding: 8px 16px;">← Back to Login</a>
    </div>
    <h1>Two-Factor Verification</h1>
    <p>Please enter the verification code sent to <strong>{{ $verificationEmail }}</strong>.</p>

    <p id="otp-delivery-timer" style="margin-bottom: 14px; font-weight: 600;"></p>

    <form method="POST" action="/verify-2fa" id="verify-form" onsubmit="showSpinner();">
        @csrf
        <div class="field">
            <label for="verification_code">Verification Code</label>
            <input type="text" id="verification_code" name="verification_code" required>
            @error('verification_code')
                <p class="error" style="margin-top: 8px; background:none; border:none; color:#b91c1c;">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="button" id="verify-button">Verify</button>
        <span id="verify-spinner" style="display:none; margin-left: 12px; color:#2563eb;">Verifying...</span>
    </form>

    <div style="margin-top: 24px;">
        <form method="POST" action="/resend-2fa" id="resend-form" style="display: none;">
            @csrf
            <button type="submit" id="resend-button" class="button">Resend code</button>
        </form>
    </div>

    <script>
        function showSpinner() {
            document.getElementById('verify-button').disabled = true;
            document.getElementById('verify-spinner').style.display = 'inline';
        }

        (function() {
            var timerElement = document.getElementById('otp-delivery-timer');
            if (!timerElement) {
                return;
            }

            var seconds = {{ $timeRemaining ?? 0 }};
            var hasWrongOtp = {{ $errors->has('verification_code') ? 'true' : 'false' }};
            var showResend = {{ $showResend ? 'true' : 'false' }};
            var resendForm = document.getElementById('resend-form');

            function formatTime(value) {
                var mins = Math.floor(value / 60).toString().padStart(2, '0');
                var secs = (value % 60).toString().padStart(2, '0');
                return mins + ':' + secs;
            }

            function showResendButton() {
                if (resendForm) {
                    resendForm.style.display = 'block';
                }
            }

            function updateDeliveryTimer() {
                if (seconds <= 0) {
                    timerElement.textContent = 'OTP has expired. Resend code is available now.';
                    showResendButton();
                    return;
                }

                var message = 'OTP expires in ' + formatTime(seconds) + '.';
                if (hasWrongOtp) {
                    message += ' Wrong OTP entered. Please try again.';
                }

                timerElement.textContent = message;
                seconds -= 1;
                setTimeout(updateDeliveryTimer, 1000);
            }

            if (showResend && seconds <= 0) {
                showResendButton();
            }

            updateDeliveryTimer();
        })();
    </script>
@endsection

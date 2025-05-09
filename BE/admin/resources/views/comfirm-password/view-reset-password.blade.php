<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-hover: #0284c7;
            --error: #ef4444;
            --success: #10b981;
            --background: #e0f7ff;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-secondary: #64748b;
            --border: #cbd5e1;
            --input-focus: rgba(14, 165, 233, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
        }

        body {
            background-color: var(--background);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        h1 {
            text-align: center;
            color: var(--primary);
            font-size: 24px;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text);
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input[type="password"],
        .input-wrapper input[type="text"] {
            width: 100%;
            padding: 10px 44px 10px 12px;
            /* đủ chỗ cho icon */
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #fff;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--input-focus);
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            padding: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            z-index: 2;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
            pointer-events: none;
        }

        .password-strength {
            height: 5px;
            background-color: #e5e7eb;
            border-radius: 4px;
            margin-top: 8px;
        }

        .password-strength-bar {
            height: 5px;
            width: 0%;
            background-color: var(--success);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .password-strength-text {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .validation-message {
            display: flex;
            align-items: center;
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 10px;
        }

        .validation-message svg {
            width: 16px;
            height: 16px;
            margin-right: 6px;
            color: var(--primary);
        }

        .buttons {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        button {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-cancel {
            background-color: #e5e7eb;
            color: var(--text);
        }

        .btn-reset {
            background-color: var(--primary);
            color: white;
            padding: 20px 50px;
        }

        .btn-reset:disabled {
            background-color: #93c5fd;
            cursor: not-allowed;
            padding: 20px 50px;
        }

        @media (max-width: 500px) {
            .buttons {
                flex-direction: column;
                gap: 10px;
            }

            .btn-cancel,
            .btn-reset {
                width: 100%;
            }
        }
    </style>


</head>

<body>
    <div class="container">
        <h1>Đặt lại mật khẩu của bạn</h1>
        <form id="resetPasswordForm">
            <div class="form-group">
                <label for="password">Mật Khẩu Mới</label>
                <input type="hidden" name="email" id="email" value="{{ $email ?? '' }}">
                <input type="hidden" name="token" id="token" value="{{ $token ?? '' }}">

                <div class="input-wrapper">
                    <input type="password" id="password" name="password" autocomplete="new-password" required>
                    <button type="button" class="toggle-password" data-target="password">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar"></div>
                </div>
                <div class="password-strength-text"></div>
                <div class="validation-message" id="passwordValidation">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Phải có ít nhất 8 ký tự
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Xác nhận mật khẩu</label>
                <div class="input-wrapper">
                    <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="new-password"
                        required>
                    <button type="button" class="toggle-password" data-target="confirmPassword">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <div class="validation-message" id="confirmPasswordValidation">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Cả hai mật khẩu phải trùng nhau
                </div>
            </div>

            <div class="buttons">
                <button type="submit" class="btn-reset" id="resetButton" disabled>Xác Nhận</button>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.toggle-password').click(function() {
                const targetId = $(this).data('target');
                const input = $('#' + targetId);

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).html(`
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    `);
                } else {
                    input.attr('type', 'password');
                    $(this).html(`
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    `);
                }
            });

            $('#password, #confirmPassword').on('input', function() {
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();
                let isValid = true;

                if (password.length < 8) {
                    $('#passwordValidation').removeClass('success').addClass('error');
                    $('#passwordValidation').html(`
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Phải có ít nhất 8 ký tự
                    `);
                    isValid = false;
                } else {
                    $('#passwordValidation').removeClass('error').addClass('success');
                    $('#passwordValidation').html(`
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Mật khẩu hợp lệ
                    `);
                }

                if (password !== confirmPassword || confirmPassword === '') {
                    $('#confirmPasswordValidation').removeClass('success').addClass('error');
                    $('#confirmPasswordValidation').html(`
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Cả hai mật khẩu phải trùng nhau
                    `);
                    isValid = false;
                } else {
                    $('#confirmPasswordValidation').removeClass('error').addClass('success');
                    $('#confirmPasswordValidation').html(`
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Mật khẩu trùng khớp
                    `);
                }

                const strength = checkPasswordStrength(password);
                updatePasswordStrength(strength);

                $('#resetButton').prop('disabled', !isValid);
            });

            function checkPasswordStrength(password) {
                let strength = 0;

                if (password.length >= 8) strength += 25;
                if (password.match(/[a-z]+/)) strength += 25;
                if (password.match(/[A-Z]+/)) strength += 25;
                if (password.match(/[0-9]+/) || password.match(/[^a-zA-Z0-9]+/)) strength += 25;

                return strength;
            }

            function updatePasswordStrength(strength) {
                $('.password-strength-bar').css('width', strength + '%');

                if (strength < 25) {
                    $('.password-strength-bar').css('backgroundColor', '#ef4444');
                    $('.password-strength-text').text('Yếu').css('color', '#ef4444');
                } else if (strength < 50) {
                    $('.password-strength-bar').css('backgroundColor', '#f97316');
                    $('.password-strength-text').text('Trung bình').css('color', '#f97316');
                } else if (strength < 75) {
                    $('.password-strength-bar').css('backgroundColor', '#eab308');
                    $('.password-strength-text').text('Tốt').css('color', '#eab308');
                } else {
                    $('.password-strength-bar').css('backgroundColor', '#10b981');
                    $('.password-strength-text').text('Mạnh').css('color', '#10b981');
                }
            }

            $('#resetPasswordForm').submit(function(e) {
                e.preventDefault();

                const email = $('#email').val();
                const token = $('#token').val();
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();

                $.ajax({
                    url: '/api/reset-password',
                    type: 'POST',
                    data: {
                        email: email,
                        token: token,
                        password: password,
                        password_confirmation: confirmPassword,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Đã có lỗi xảy ra.';
                        alert(error);
                    }
                });

            });
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ __('general.payment_redirecting') }}</title>
        <style>
            :root {
                color-scheme: light;
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                font-family: Tahoma, "Segoe UI", sans-serif;
                background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
                color: #18181b;
            }

            .card {
                width: min(100%, 28rem);
                margin: 1.5rem;
                padding: 2rem 1.5rem;
                text-align: center;
                background: #fff;
                border: 1px solid #e4e4e7;
                border-radius: 1rem;
                box-shadow: 0 10px 30px rgba(24, 24, 27, 0.06);
            }

            .spinner {
                margin: 0 auto 1.5rem;
                width: 70px;
                text-align: center;
            }

            .spinner > div {
                width: 14px;
                height: 14px;
                margin: 0 3px;
                background-color: #0d9488;
                border-radius: 100%;
                display: inline-block;
                animation: bounce 1.4s infinite ease-in-out both;
            }

            .spinner .bounce1 { animation-delay: -0.32s; }
            .spinner .bounce2 { animation-delay: -0.16s; }

            @keyframes bounce {
                0%, 80%, 100% { transform: scale(0); }
                40% { transform: scale(1); }
            }

            h1 {
                margin: 0 0 0.75rem;
                font-size: 1.15rem;
                font-weight: 700;
            }

            p {
                margin: 0 0 0.75rem;
                line-height: 1.7;
                color: #52525b;
                font-size: 0.95rem;
            }

            button {
                margin-top: 0.5rem;
                border: 0;
                border-radius: 0.75rem;
                background: #0d9488;
                color: #fff;
                padding: 0.75rem 1.25rem;
                font: inherit;
                cursor: pointer;
            }

            button:hover {
                background: #0f766e;
            }

            #countdown {
                font-weight: 700;
                color: #0f766e;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="spinner" aria-hidden="true">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>

            <h1>{{ __('general.payment_redirecting') }}</h1>
            <p>{{ __('general.payment_redirecting_hint') }}</p>
            <p>
                {{ __('general.payment_redirect_fallback') }}
                <span id="countdown">3</span>
                {{ __('general.seconds') }}
            </p>

            <form id="payment-redirect-form" method="{{ $method }}" action="{{ $action }}">
                @foreach($inputs as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach

                <button type="submit">{{ __('general.payment_continue') }}</button>
            </form>
        </div>

        <script>
            (function () {
                var form = document.getElementById('payment-redirect-form');
                var countdownEl = document.getElementById('countdown');
                var seconds = 3;

                function submitForm() {
                    if (form) {
                        form.submit();
                    }
                }

                function tick() {
                    seconds -= 1;

                    if (seconds <= 0) {
                        submitForm();
                        return;
                    }

                    if (countdownEl) {
                        countdownEl.textContent = String(seconds);
                    }

                    window.setTimeout(tick, 1000);
                }

                submitForm();
                window.setTimeout(tick, 1000);
            })();
        </script>
    </body>
</html>

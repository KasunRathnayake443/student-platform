<div>
    <style>
        .compose-page {
            padding: 24px 28px;
            max-width: 100%;
            box-sizing: border-box;
        }

        .compose-shell {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
        }

        .compose-note {
            font-size: 11.5px;
            color: #94a3b8;
            line-height: 1.6;
            padding: 16px 22px 6px;
        }

        .compose-ctn {
            padding: 8px 22px 22px;
        }

        .compose-shell .fi-fo-field-wrp,
        .compose-shell [data-field-wrapper] {
            margin-bottom: 16px;
        }
    </style>

    <div class="compose-page">
        <div class="compose-shell">
            <p class="compose-note">
                Emails are sent from the school's own SMTP settings and are delivered to each selected student.
                A parent or guardian copy is added automatically when a parent email is on file and the option is left on.
                Messages are queued, so a worker must be running.
            </p>

            <div class="compose-ctn">
                {{ $this->content }}
            </div>
        </div>
    </div>
</div>

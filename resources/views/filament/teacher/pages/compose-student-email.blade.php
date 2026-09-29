<div>
    <style>
        .compose-shell {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
        }

        .compose-note {
            font-size: 11.5px;
            color: #94a3b8;
            padding: 14px 20px 0;
        }

        .compose-shell .fi-fo-field-wrp,
        .compose-shell [data-field-wrapper] {
            margin-bottom: 4px;
        }
    </style>

    <div class="compose-shell">
        <p class="compose-note">
            Emails are sent from the school's own SMTP settings and are delivered to each selected student.
            A parent or guardian copy is added automatically when a parent email is on file and the option is left on.
            Messages are queued, so a worker must be running.
        </p>

        {{ $this->content }}
    </div>
</div>

<x-filament-panels::page.simple>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    .student-login-wrap {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at 10% 20%, #ede9fe 0%, #e0e7ff 45%, #f8fafc 100%);
        padding: 2rem 1rem;
        position: relative;
        overflow: hidden;
        width: 100%;
    }

    /* Ambient Floating Glow Spheres */
    .glow-sphere {
        position: absolute;
        border-radius: 50%;
        filter: blur(90px);
        opacity: 0.45;
        pointer-events: none;
    }
    .glow-sphere-1 {
        width: 500px;
        height: 500px;
        background: #c4b5fd;
        top: -10%;
        left: -10%;
    }
    .glow-sphere-2 {
        width: 450px;
        height: 450px;
        background: #fbcfe8;
        bottom: -10%;
        right: -10%;
    }

    /* Filament Page Container Overrides */
    .fi-simple-main {
        max-width: 980px !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 auto !important;
    }
    .fi-simple-main-ctn {
        padding: 1rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 100vh !important;
    }

    /* Widescreen Split Card - Light Theme */
    .desktop-login-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 2rem;
        box-shadow: 0 25px 60px -15px rgba(99, 102, 241, 0.15), 0 10px 30px -5px rgba(0, 0, 0, 0.04);
        width: 100%;
        display: grid;
        grid-template-columns: 1fr 1.1fr;
        overflow: hidden;
        position: relative;
        z-index: 10;
    }

    @media (max-width: 768px) {
        .desktop-login-card {
            grid-template-columns: 1fr;
        }
        .left-hero-panel {
            display: none !important;
        }
    }

    /* Left Panel - Hero Branding */
    .left-hero-panel {
        padding: 3.5rem 3rem;
        background: linear-gradient(145deg, #7c3aed 0%, #6d28d9 50%, #4c1d95 100%);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: #ffffff;
        position: relative;
    }

    .brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.95rem;
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 9999px;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.82rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        width: fit-content;
        backdrop-filter: blur(8px);
    }

    .hero-body h2 {
        font-size: 2.25rem;
        font-weight: 800;
        line-height: 1.25;
        margin-top: 1.25rem;
        margin-bottom: 0.75rem;
        color: #ffffff;
    }

    .hero-body p {
        color: rgba(255, 255, 255, 0.88);
        font-size: 0.98rem;
        line-height: 1.6;
        font-weight: 500;
    }

    .hero-features {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        margin-top: 1.75rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        color: #ffffff;
        font-weight: 600;
    }

    .feature-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.6rem;
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .hero-footer {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.7);
        font-weight: 600;
        margin-top: 2rem;
    }

    /* Right Panel - Login Form (Light Theme) */
    .right-form-panel {
        padding: 3.5rem 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #ffffff;
    }

    .form-header-title {
        margin-bottom: 2rem;
    }

    .form-header-title h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin-bottom: 0.35rem;
    }

    .form-header-title p {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* Form Overrides - High Contrast Light Theme */
    .right-form-panel form {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .right-form-panel label,
    .right-form-panel .fi-fo-field-label,
    .right-form-panel .fi-fo-field-label-content,
    .right-form-panel .fi-checkbox-label,
    .right-form-panel p,
    .right-form-panel span:not(.fi-btn-label) {
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 0.88rem !important;
    }

    .right-form-panel .fi-fo-field-wrp-error-message,
    .right-form-panel .fi-fo-field-error-message,
    .right-form-panel [data-field-wrapper-error-message] {
        color: #dc2626 !important;
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        margin-top: 0.35rem !important;
    }

    .right-form-panel input[type="email"],
    .right-form-panel input[type="password"] {
        background-color: #f8fafc !important;
        border: 2px solid #e2e8f0 !important;
        border-radius: 0.85rem !important;
        padding: 0.85rem 1.1rem !important;
        color: #0f172a !important;
        font-size: 0.95rem !important;
        font-weight: 600 !important;
        transition: all 0.2s !important;
        box-shadow: none !important;
    }

    .right-form-panel input[type="email"]:focus,
    .right-form-panel input[type="password"]:focus {
        border-color: #7c3aed !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12) !important;
    }

    .right-form-panel button[type="submit"] {
        background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
        padding: 0.95rem !important;
        border-radius: 0.85rem !important;
        box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.3) !important;
        transition: all 0.2s !important;
        border: none !important;
        cursor: pointer !important;
        margin-top: 0.75rem !important;
    }

    .right-form-panel button[type="submit"]:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 15px 30px -5px rgba(124, 58, 237, 0.4) !important;
    }

    .right-form-panel button[type="submit"] span {
        color: #ffffff !important;
    }

    .fi-simple-header {
        display: none !important;
    }
    </style>

    <div class="student-login-wrap">
        <div class="glow-sphere glow-sphere-1"></div>
        <div class="glow-sphere glow-sphere-2"></div>

        <div class="desktop-login-card">
            <!-- Left Panel: Hero Branding -->
            <div class="left-hero-panel">
                <div>
                    <div class="brand-badge">
                        <span>🎓 Student Platform</span>
                    </div>

                    <div class="hero-body">
                        <h2>Empower Your Learning Journey</h2>
                        <p>Access your personal learning workspace, connect with your classes, complete assignments, and track your progress in real time.</p>
                        
                        <div class="hero-features">
                            <div class="feature-item">
                                <div class="feature-icon">🚀</div>
                                <span>Personalized Learning Workspace</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">📚</div>
                                <span>Interactive Quizzes & Assignment Hub</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">🏆</div>
                                <span>Live Academic Progress Tracking</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hero-footer">
                    &copy; {{ now()->format('Y') }} Student Platform. All rights reserved.
                </div>
            </div>

            <!-- Right Panel: Simple Login Form -->
            <div class="right-form-panel">
                <div class="form-header-title">
                    <h1>Welcome Back! 👋</h1>
                    <p>Please enter your student credentials to log in.</p>
                </div>

                {{ $this->content }}
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>

<style>
/* ───── Premium Light Login Styles ───── */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.student-login-wrap {
    font-family: 'Inter', system-ui, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 10% 20%, rgba(216, 180, 254, 0.4) 0%, rgba(243, 232, 255, 0.4) 90%), #ffffff;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

/* Background floating decorative circles */
.student-login-wrap::before {
    content: '';
    position: absolute;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(167, 139, 250, 0.15) 0%, transparent 60%);
    top: -200px;
    left: -200px;
    border-radius: 50%;
}
.student-login-wrap::after {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(236, 72, 153, 0.1) 0%, transparent 60%);
    bottom: -150px;
    right: -150px;
    border-radius: 50%;
}

.login-container {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 2rem;
    box-shadow: 0 20px 50px rgba(124, 58, 237, 0.12);
    width: 100%;
    max-width: 960px;
    display: grid;
    grid-template-columns: 1fr 1.1fr;
    overflow: hidden;
    position: relative;
    z-index: 2;
}

@media (max-width: 768px) {
    .login-container {
        grid-template-columns: 1fr;
    }
    .login-art-panel {
        display: none;
    }
}

/* Left panel - Art & Motivation */
.login-art-panel {
    background: linear-gradient(135deg, #8b5cf6 0%, #d946ef 100%);
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: #ffffff;
    position: relative;
    overflow: hidden;
}
.login-art-panel::before {
    content: '🎓';
    position: absolute;
    font-size: 15rem;
    opacity: 0.12;
    bottom: -2rem;
    right: -1rem;
    transform: rotate(-15deg);
}
.art-header {
    font-size: 1.1rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.9);
}
.art-body h2 {
    font-size: 2.25rem;
    font-weight: 900;
    line-height: 1.25;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.art-body p {
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.6;
    font-weight: 500;
}
.art-footer {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 600;
}

/* Right panel - Form */
.login-form-panel {
    padding: 3.5rem 3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.login-form-header {
    margin-bottom: 2.25rem;
}
.login-form-header h1 {
    font-size: 1.8rem;
    font-weight: 900;
    color: #1e1b4b;
    margin-bottom: 0.4rem;
}
.login-form-header p {
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 500;
}

/* Custom Overrides for Filament form components to ensure light theme styling */
.login-form-panel form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.login-form-panel label {
    font-weight: 700 !important;
    color: #4b5563 !important;
    font-size: 0.85rem !important;
}
.login-form-panel input[type="email"],
.login-form-panel input[type="password"] {
    background-color: #f9fafb !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1rem !important;
    color: #111827 !important;
    font-size: 0.95rem !important;
    transition: all 0.2s !important;
    box-shadow: none !important;
}
.login-form-panel input[type="email"]:focus,
.login-form-panel input[type="password"]:focus {
    border-color: #8b5cf6 !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.12) !important;
}
.login-form-panel button[type="submit"] {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 0.95rem !important;
    padding: 0.85rem !important;
    border-radius: 0.75rem !important;
    box-shadow: 0 10px 25px rgba(124, 58, 237, 0.25) !important;
    transition: all 0.2s !important;
    border: none !important;
    cursor: pointer !important;
    margin-top: 0.5rem !important;
}
.login-form-panel button[type="submit"]:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 12px 30px rgba(124, 58, 237, 0.35) !important;
}
.login-form-panel .fi-fo-field-wrapper {
    margin-bottom: 0.5rem;
}
.login-form-panel .fi-checkbox-input {
    border-radius: 0.375rem !important;
    border-color: #d1d5db !important;
    color: #8b5cf6 !important;
}
.login-form-panel .fi-checkbox-input:checked {
    background-color: #8b5cf6 !important;
    border-color: #8b5cf6 !important;
}
</style>

<div class="student-login-wrap">
    <div class="login-container">
        
        <!-- Left Panel: Graphic & Text -->
        <div class="login-art-panel">
            <div class="art-header">🎓 Student Portal</div>
            <div class="art-body">
                <h2>Empower Your Learning Journey</h2>
                <p>Access your classes, complete exciting missions, track your achievements, and connect with your teachers all in one interactive space.</p>
            </div>
            <div class="art-footer">&copy; {{ now()->format('Y') }} Student Platform. All rights reserved.</div>
        </div>
        
        <!-- Right Panel: Form -->
        <div class="login-form-panel">
            <div class="login-form-header">
                <h1>Welcome Back!</h1>
                <p>Please enter your credentials to log in.</p>
            </div>
            
            <form wire:submit.prevent="authenticate">
                {{ $this->form }}
                
                <button type="submit">
                    Log In to My Dashboard 🚀
                </button>
            </form>
        </div>
        
    </div>
</div>

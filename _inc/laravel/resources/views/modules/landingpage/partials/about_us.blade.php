{{-- About Us — fallback content when DB content is empty --}}
@php
    // Load testimonials from JSON file
    $testimonialsJsonPath = base_path('Modules/LandingPage/Config/blobs/testimonials.json');
    $allTestimonials = [];
    if (file_exists($testimonialsJsonPath)) {
        $testimonialsJson = file_get_contents($testimonialsJsonPath);
        $allTestimonials = json_decode($testimonialsJson, true) ?? [];
    }
    
    // Randomize and pick 4 testimonials for display
    shuffle($allTestimonials);
    $displayTestimonials = array_slice($allTestimonials, 0, 4);
@endphp

{{-- Dynamic Animations CSS with Blue/Jade Color Scheme --}}
<style>
    :root {
        /* Primary Blue Palette */
        --prestech-blue-dark: #0a1628;
        --prestech-blue: #1a365d;
        --prestech-blue-medium: #2c5282;
        --prestech-blue-light: #3182ce;
        --prestech-blue-lighter: #63b3ed;
        --prestech-blue-pale: #bee3f8;
        
        /* Jade/Green Accents */
        --prestech-jade-dark: #0d503c;
        --prestech-jade: #10b981;
        --prestech-jade-light: #34d399;
        --prestech-jade-pale: #a7f3d0;
        
        /* Gradient combinations */
        --gradient-blue: linear-gradient(135deg, var(--prestech-blue-dark) 0%, var(--prestech-blue) 50%, var(--prestech-blue-medium) 100%);
        --gradient-blue-jade: linear-gradient(135deg, var(--prestech-blue) 0%, var(--prestech-blue-medium) 50%, var(--prestech-jade-dark) 100%);
        --gradient-jade-accent: linear-gradient(135deg, var(--prestech-jade) 0%, var(--prestech-jade-light) 100%);
    }

    /* Base animation utilities */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-40px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(40px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        50% { transform: scale(1.02); box-shadow: 0 0 20px 5px rgba(16, 185, 129, 0.2); }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes gradientFlow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    @keyframes glow {
        0%, 100% { box-shadow: 0 0 5px rgba(16, 185, 129, 0.5), 0 0 10px rgba(49, 130, 206, 0.3); }
        50% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.8), 0 0 30px rgba(49, 130, 206, 0.5); }
    }

    @keyframes slideInFromLeft {
        from { opacity: 0; transform: translateX(-100px) rotate(-5deg); }
        to { opacity: 1; transform: translateX(0) rotate(0); }
    }

    @keyframes slideInFromRight {
        from { opacity: 0; transform: translateX(100px) rotate(5deg); }
        to { opacity: 1; transform: translateX(0) rotate(0); }
    }

    @keyframes bounceIn {
        0% { opacity: 0; transform: scale(0.3); }
        50% { opacity: 1; transform: scale(1.05); }
        70% { transform: scale(0.9); }
        100% { transform: scale(1); }
    }

    /* Scroll margin for navigation */
    .scroll-mt-5 { scroll-margin-top: 100px; }
    html { scroll-behavior: smooth; }

    /* About page wrapper with blue gradient background */
    .about-page-wrapper {
        background: linear-gradient(180deg, 
            var(--prestech-blue-dark) 0%, 
            var(--prestech-blue) 15%,
            var(--prestech-blue-medium) 40%,
            #1e3a5f 70%,
            var(--prestech-blue-dark) 100%
        );
        min-height: 100vh;
        padding: 2rem 0;
        position: relative;
        overflow: hidden;
    }

    .about-page-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(ellipse at 20% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 80%, rgba(99, 179, 237, 0.1) 0%, transparent 50%),
            radial-gradient(ellipse at 50% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Main about card */
    .about-card {
        position: relative;
        background: linear-gradient(145deg, 
            rgba(255, 255, 255, 0.95) 0%, 
            rgba(248, 250, 252, 0.98) 50%,
            rgba(240, 249, 255, 0.95) 100%
        );
        border-radius: 1.5rem;
        overflow: hidden;
        animation: fadeInUp 0.8s ease-out forwards;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(49, 130, 206, 0.2);
        box-shadow: 
            0 25px 50px -12px rgba(10, 22, 40, 0.4),
            0 0 0 1px rgba(16, 185, 129, 0.1) inset;
    }

    .about-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, 
            var(--prestech-blue) 0%, 
            var(--prestech-jade) 25%, 
            var(--prestech-blue-light) 50%, 
            var(--prestech-jade-light) 75%, 
            var(--prestech-blue) 100%
        );
        background-size: 200% 100%;
        animation: gradientFlow 4s ease infinite;
    }

    /* Animated heading with blue/jade gradient */
    .about-heading {
        background: linear-gradient(135deg, var(--prestech-blue) 0%, var(--prestech-jade) 50%, var(--prestech-blue-light) 100%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: fadeInLeft 0.6s ease-out 0.2s both, gradientFlow 5s ease infinite;
    }

    /* Lead paragraph animation */
    .about-lead {
        animation: fadeInUp 0.7s ease-out 0.3s both;
        color: var(--prestech-blue-dark);
    }

    .about-intro {
        animation: fadeInUp 0.7s ease-out 0.4s both;
        color: #334155;
    }

    /* Feature cards with blue/jade styling */
    .feature-card {
        position: relative;
        background: linear-gradient(145deg, 
            rgba(255, 255, 255, 0.9) 0%, 
            rgba(240, 249, 255, 0.95) 100%
        );
        border: 1px solid rgba(49, 130, 206, 0.15);
        border-radius: 1rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
    }

    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.1), transparent);
        transition: left 0.5s;
    }

    .feature-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--gradient-jade-accent);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .feature-card:hover::before { left: 100%; }
    .feature-card:hover::after { transform: scaleX(1); }

    .feature-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 
            0 20px 40px rgba(26, 54, 93, 0.2),
            0 0 0 2px var(--prestech-jade);
        border-color: var(--prestech-jade);
    }

    .feature-card:nth-child(1) { animation: slideInFromLeft 0.6s ease-out 0.5s both; }
    .feature-card:nth-child(2) { animation: bounceIn 0.7s ease-out 0.6s both; }
    .feature-card:nth-child(3) { animation: slideInFromRight 0.6s ease-out 0.7s both; }

    .feature-icon {
        font-size: 2.5rem;
        display: inline-block;
        animation: float 3s ease-in-out infinite;
        filter: drop-shadow(0 4px 6px rgba(16, 185, 129, 0.3));
    }

    .feature-card:nth-child(1) .feature-icon { animation-delay: 0s; }
    .feature-card:nth-child(2) .feature-icon { animation-delay: 0.5s; }
    .feature-card:nth-child(3) .feature-icon { animation-delay: 1s; }

    .feature-card h5 {
        color: var(--prestech-blue);
        transition: color 0.3s ease;
    }

    .feature-card:hover h5 {
        color: var(--prestech-jade);
    }

    /* Commitment section with blue/jade gradient */
    .commitment-section {
        background: linear-gradient(135deg, 
            var(--prestech-blue) 0%, 
            var(--prestech-blue-medium) 40%,
            var(--prestech-jade-dark) 100%
        );
        border-radius: 1.5rem;
        padding: 2.5rem;
        color: white;
        animation: scaleIn 0.7s ease-out 0.8s both;
        position: relative;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(10, 22, 40, 0.5);
    }

    .commitment-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: 
            radial-gradient(circle at 30% 30%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 70% 70%, rgba(99, 179, 237, 0.1) 0%, transparent 50%);
        animation: pulse 4s ease-in-out infinite;
    }

    .commitment-list li {
        opacity: 0;
        animation: fadeInLeft 0.5s ease-out forwards;
        margin-bottom: 1rem;
        position: relative;
        padding-left: 2rem;
        color: rgba(255, 255, 255, 0.95);
    }

    .commitment-list li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--prestech-jade-light);
        font-weight: bold;
        font-size: 1.2rem;
        text-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
    }

    .commitment-list li:nth-child(1) { animation-delay: 0.9s; }
    .commitment-list li:nth-child(2) { animation-delay: 1s; }
    .commitment-list li:nth-child(3) { animation-delay: 1.1s; }

    /* FAQ accordion styling */
    .faq-section { animation: fadeInUp 0.7s ease-out 1.2s both; }

    .accordion-button {
        transition: all 0.3s ease;
        background: linear-gradient(145deg, #ffffff, #f0f9ff);
        color: var(--prestech-blue);
        font-weight: 500;
    }

    .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, var(--prestech-blue) 0%, var(--prestech-jade-dark) 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(26, 54, 93, 0.3);
    }

    .accordion-button:hover { transform: translateX(5px); }

    .accordion-button:focus {
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
    }

    .accordion-body {
        background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);
        animation: fadeInUp 0.3s ease-out;
        border-left: 3px solid var(--prestech-jade);
    }

    /* Testimonial section with grid */
    .testimonial-section { animation: fadeInUp 0.7s ease-out 1.4s both; }

    .testimonial-card {
        background: linear-gradient(145deg, 
            rgba(255, 255, 255, 0.98) 0%, 
            rgba(240, 249, 255, 0.95) 100%
        );
        border-radius: 1.25rem;
        padding: 1.5rem;
        border: 1px solid rgba(49, 130, 206, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .testimonial-card::before {
        content: '"';
        position: absolute;
        top: -15px;
        left: 15px;
        font-size: 7rem;
        color: rgba(16, 185, 129, 0.08);
        font-family: Georgia, serif;
        line-height: 1;
    }

    .testimonial-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-jade-accent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .testimonial-card:hover::after { opacity: 1; }

    .testimonial-card:hover {
        transform: translateY(-10px);
        box-shadow: 
            0 25px 50px rgba(26, 54, 93, 0.15),
            0 0 0 2px var(--prestech-jade);
    }

    .testimonial-card:nth-child(1) { animation: slideInFromLeft 0.6s ease-out 1.5s both; }
    .testimonial-card:nth-child(2) { animation: bounceIn 0.7s ease-out 1.6s both; }
    .testimonial-card:nth-child(3) { animation: slideInFromRight 0.6s ease-out 1.7s both; }
    .testimonial-card:nth-child(4) { animation: fadeInUp 0.6s ease-out 1.8s both; }

    .testimonial-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--prestech-jade);
        box-shadow: 
            0 4px 15px rgba(16, 185, 129, 0.3),
            0 0 0 3px rgba(49, 130, 206, 0.1);
        transition: all 0.3s ease;
    }

    .testimonial-card:hover .testimonial-avatar {
        transform: scale(1.1);
        box-shadow: 
            0 6px 20px rgba(16, 185, 129, 0.4),
            0 0 0 4px var(--prestech-jade-light);
        animation: glow 2s ease-in-out infinite;
    }

    .testimonial-quote {
        position: relative;
        font-style: italic;
        color: #475569;
        line-height: 1.7;
        flex-grow: 1;
    }

    .testimonial-name {
        background: linear-gradient(135deg, var(--prestech-blue) 0%, var(--prestech-jade) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        font-size: 1.05rem;
    }

    .testimonial-designation {
        color: var(--prestech-jade);
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Star rating */
    .star-rating {
        color: #fbbf24;
        font-size: 1rem;
        margin-bottom: 0.75rem;
        text-shadow: 0 1px 2px rgba(251, 191, 36, 0.3);
    }

    .star-rating .empty-star {
        color: #d1d5db;
    }

    /* CTA section with animated blue/jade gradient */
    .cta-section {
        background: linear-gradient(-45deg, 
            var(--prestech-blue-dark), 
            var(--prestech-blue), 
            var(--prestech-jade-dark), 
            var(--prestech-blue-medium)
        );
        background-size: 400% 400%;
        animation: gradientFlow 10s ease infinite, scaleIn 0.7s ease-out 1.8s both;
        border-radius: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(10, 22, 40, 0.5);
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(ellipse at 20% 80%, rgba(16, 185, 129, 0.3) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 20%, rgba(99, 179, 237, 0.2) 0%, transparent 50%);
    }

    .cta-btn {
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
        overflow: hidden;
        background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
        color: var(--prestech-blue);
        font-weight: 600;
        border: 2px solid transparent;
    }

    .cta-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.3), transparent);
        transition: left 0.5s;
    }

    .cta-btn:hover::before { left: 100%; }

    .cta-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, var(--prestech-jade-light) 0%, var(--prestech-jade) 100%);
        color: white;
    }

    /* Section headings */
    .section-heading {
        position: relative;
        display: inline-block;
        margin-bottom: 1.5rem;
        color: var(--prestech-blue);
    }

    .section-heading::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 4px;
        background: var(--gradient-jade-accent);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .section-heading:hover::after { width: 100%; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .about-page-wrapper { padding: 1rem 0; }
        .testimonial-card { margin-bottom: 1rem; }
        .commitment-section { padding: 1.5rem; }
        .feature-card { margin-bottom: 1rem; }
    }
</style>

<div class="about-page-wrapper">
    <div class="container">
        <div class="about-card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                <h2 class="mb-4 about-heading fw-bold">{{ __('About Nova Prestech') }}</h2>

                <p class="lead about-lead">{!! __('With over <strong>30 years of experience</strong> in the IT industry, <strong>Nova Prestech.net Informática</strong> delivers comprehensive technology solutions — from infrastructure and cybersecurity to custom software development and digital transformation.') !!}</p>

                <p class="about-intro">{{ __('Headquartered in Rio de Janeiro, Brazil, we serve businesses of all sizes with a humanized approach, combining technical reliability with practical innovation to turn technology into real value for your operations.') }}</p>

                {{-- Features Section --}}
                <section id="features-section" class="scroll-mt-5 mt-5">
                    <h4 class="section-heading">{{ __('Our Services') }}</h4>
                    <div class="row mt-4 mb-4" id="features">
                        <div class="col-md-4 mb-3">
                            <div class="feature-card p-4 h-100">
                                <div class="feature-icon mb-3">🛡️</div>
                                <h5 class="fw-bold">{{ __('Cybersecurity') }}</h5>
                                <p class="mb-0 text-muted">{{ __('Network protection, threat monitoring, firewall management, and compliance auditing with industry-leading partners like Fortinet.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="feature-card p-4 h-100">
                                <div class="feature-icon mb-3">🏗️</div>
                                <h5 class="fw-bold">{{ __('IT Infrastructure') }}</h5>
                                <p class="mb-0 text-muted">{{ __('Server deployment, cloud migration, network architecture, and ongoing managed services to keep your business running.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="feature-card p-4 h-100">
                                <div class="feature-icon mb-3">💻</div>
                                <h5 class="fw-bold">{{ __('Software Development') }}</h5>
                                <p class="mb-0 text-muted">{{ __('Custom web and mobile applications, ERP systems, process automation, and API integrations tailored to your workflow.') }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Plan/Commitment Section --}}
                <section id="plan-section" class="scroll-mt-5 mt-5">
                    <div class="commitment-section">
                        <h4 class="section-heading text-white mb-4">{{ __('Our Commitment') }}</h4>
                        <ul class="commitment-list list-unstyled mb-0">
                            <li><strong>{{ __('SLA-backed support') }}</strong>: {{ __('99.5% uptime guarantee with priority response within 4 business hours.') }}</li>
                            <li><strong>{{ __('Dedicated team') }}</strong>: {{ __('Certified professionals in Microsoft, Red Hat, Fortinet, and cloud platforms.') }}</li>
                            <li><strong>{{ __('Transparency') }}</strong>: {{ __('Clear reporting, honest communication, and no hidden fees.') }}</li>
                        </ul>
                    </div>
                </section>

                {{-- FAQ Section --}}
                <section id="faq-section" class="scroll-mt-5 mt-5 faq-section">
                    <h4 class="section-heading">{{ __('Frequently Asked Questions') }}</h4>
                    <div class="accordion mt-4" id="aboutFaq">
                        <div class="accordion-item border-0 mb-2 rounded overflow-hidden shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    {{ __('What services does Nova Prestech offer?') }}
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#aboutFaq">
                                <div class="accordion-body">
                                    {{ __('We offer IT infrastructure management, cybersecurity solutions, custom software development, cloud migration, technical assistance, and ERP implementation for businesses of all sizes.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-2 rounded overflow-hidden shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    {{ __('What is the ERP Nova Prestech platform?') }}
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#aboutFaq">
                                <div class="accordion-body">
                                    {{ __('ERP Nova Prestech is a comprehensive enterprise resource planning platform that integrates financial management, HR, CRM, project tracking, invoicing, and more into a unified system accessible from any browser.') }}
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-2 rounded overflow-hidden shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    {{ __('How can I contact Nova Prestech?') }}
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#aboutFaq">
                                <div class="accordion-body">
                                    {!! __('You can reach us by email at <a href="mailto:comercial@prestech.com.br" class="text-decoration-none" style="color: var(--prestech-jade);">comercial@prestech.com.br</a>, by phone at <a href="tel:+552138607510" class="text-decoration-none" style="color: var(--prestech-jade);">+55 (21) 3860-7510</a>, or visit our office at Rua Francisco Manuel, 99A — Benfica, Rio de Janeiro — RJ.') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Testimonials Section --}}
                <section id="testimonials-section" class="scroll-mt-5 mt-5 testimonial-section">
                    <h4 class="section-heading">{{ __('What Our Clients Say') }}</h4>
                    <div class="row mt-4 g-4">
                        @foreach($displayTestimonials as $index => $testimonial)
                        @php
                            $stars = intval($testimonial['testimonials_star'] ?? 5);
                            $avatarFile = basename($testimonial['testimonials_user_avatar'] ?? '');
                        @endphp
                        <div class="col-md-6 col-lg-3">
                            <div class="testimonial-card">
                                <div class="star-rating mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="{{ $i <= $stars ? '' : 'empty-star' }}">{{ $i <= $stars ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                                <p class="testimonial-quote mb-3">"{{ $testimonial['testimonials_description'] ?? '' }}"</p>
                                <div class="d-flex align-items-center mt-auto pt-3 border-top">
                                    <img 
                                        src="{{ asset('assets/images/testimonials/' . $avatarFile) }}" 
                                        alt="{{ $testimonial['testimonials_title'] ?? 'Client' }}"
                                        class="testimonial-avatar me-3"
                                        loading="lazy"
                                        onerror="this.onerror=null; this.src='{{ asset('uploads/avatar/default.png') }}';"
                                    >
                                    <div>
                                        <div class="testimonial-name">{{ $testimonial['testimonials_title'] ?? '' }}</div>
                                        <div class="testimonial-designation">{{ $testimonial['testimonials_designation'] ?? '' }}</div>
                                        @if(!empty($testimonial['testimonials_user']))
                                        <div class="text-muted small">{{ $testimonial['testimonials_user'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>

                {{-- CTA Section --}}
                <section class="cta-section text-white p-5 text-center mt-5">
                    <h4 class="mb-3 fw-bold position-relative" style="z-index: 1;">{{ __('Ready to transform your business with technology?') }}</h4>
                    <p class="mb-4 position-relative" style="z-index: 1; opacity: 0.95;">{{ __('Get in touch with our team for a free consultation and discover how we can help.') }}</p>
                    <a href="https://prestech.com.br/site/contato/" target="_blank" rel="noopener noreferrer" class="btn btn-lg rounded-pill cta-btn px-5 py-3">
                        {{ __('Contact Us') }} →
                    </a>
                </section>
            </div>
        </div>
    </div>
</div>

{{-- Newsletter Signup Component --}}
<style>
    .newsletter-section {
        background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%);
        padding: 3rem 2rem;
        border-radius: 20px;
        margin: 3rem 0;
        position: relative;
        overflow: hidden;
    }

    .newsletter-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .newsletter-inner {
        max-width: 600px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 1;
    }

    .newsletter-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
    }

    .newsletter-icon i {
        font-size: 1.75rem;
        color: white;
    }

    .newsletter-section h3 {
        font-family: 'Sora', sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.75rem;
    }

    .newsletter-section p {
        color: rgba(255, 255, 255, 0.85);
        font-size: 1rem;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .newsletter-form {
        display: flex;
        gap: 0.75rem;
        max-width: 450px;
        margin: 0 auto;
    }

    .newsletter-form input[type="email"] {
        flex: 1;
        padding: 0.875rem 1.25rem;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.2s ease;
    }

    .newsletter-form input[type="email"]::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .newsletter-form input[type="email"]:focus {
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.15);
    }

    .newsletter-form button {
        padding: 0.875rem 1.5rem;
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .newsletter-form button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
    }

    .newsletter-privacy {
        margin-top: 1rem;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
    }

    .newsletter-privacy i {
        margin-right: 0.25rem;
    }

    @media (max-width: 576px) {
        .newsletter-section {
            padding: 2rem 1.5rem;
            margin: 2rem 0;
        }

        .newsletter-section h3 {
            font-size: 1.4rem;
        }

        .newsletter-form {
            flex-direction: column;
        }

        .newsletter-form button {
            width: 100%;
        }
    }
</style>

<div class="newsletter-section">
    <div class="newsletter-inner">
        <div class="newsletter-icon">
            <i class="bi bi-envelope-heart"></i>
        </div>
        <h3>Get NYC Care Tips Monthly</h3>
        <p>Join 2,000+ families receiving expert caregiving advice, safety tips, and exclusive resources.</p>
        <form class="newsletter-form" action="{{ url('/newsletter/subscribe') }}" method="POST">
            @csrf
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Subscribe</button>
        </form>
        <p class="newsletter-privacy">
            <i class="bi bi-shield-check"></i>
            We respect your privacy. Unsubscribe anytime.
        </p>
    </div>
</div>

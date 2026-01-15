{{-- Trust Strip - Social proof bar --}}
<style>
    .trust-strip {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-top: 1px solid rgba(11, 79, 162, 0.08);
        border-bottom: 1px solid rgba(11, 79, 162, 0.08);
        padding: 1.25rem 1.5rem;
    }

    .trust-strip-inner {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 1.5rem 2.5rem;
    }

    .trust-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: #334155;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .trust-item i {
        color: #0B4FA2;
        font-size: 1.1rem;
    }

    .trust-item.verified i {
        color: #16a34a;
    }

    .trust-item.secure i {
        color: #f97316;
    }

    @media (max-width: 768px) {
        .trust-strip {
            padding: 1rem;
        }

        .trust-strip-inner {
            gap: 0.75rem 1.5rem;
        }

        .trust-item {
            font-size: 0.8rem;
        }

        .trust-item i {
            font-size: 1rem;
        }
    }
</style>

<div class="trust-strip">
    <div class="trust-strip-inner">
        <div class="trust-item">
            <i class="bi bi-shield-check"></i>
            <span>Licensed & Insured</span>
        </div>
        <div class="trust-item verified">
            <i class="bi bi-person-check-fill"></i>
            <span>Background-Checked Caregivers</span>
        </div>
        <div class="trust-item">
            <i class="bi bi-calendar2-check"></i>
            <span>Flexible Scheduling</span>
        </div>
        <div class="trust-item secure">
            <i class="bi bi-lock-fill"></i>
            <span>Secure Payments</span>
        </div>
        <div class="trust-item">
            <i class="bi bi-geo-alt-fill"></i>
            <span>Serving All NYC Boroughs</span>
        </div>
    </div>
</div>

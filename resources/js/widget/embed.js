(function () {
    'use strict';

    var SCRIPT = document.currentScript;
    var BASE_URL = SCRIPT ? SCRIPT.src.replace(/\/widget\/embed\.js.*$/, '') : '';

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function hexToRgb(hex) {
        var r = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return r
            ? parseInt(r[1], 16) + ',' + parseInt(r[2], 16) + ',' + parseInt(r[3], 16)
            : '0,0,0';
    }

    function buildStyles(settings) {
        var pRgb = hexToRgb(settings.primary_color);
        var sRgb = hexToRgb(settings.secondary_text_color);
        var cardR = parseInt(settings.card_border_radius, 10);
        var btnR = parseInt(settings.button_border_radius, 10);
        var inputR = parseInt(settings.input_border_radius, 10);
        var inputBorder = escapeHtml(settings.input_border_color || '#e5e7eb');
        var inputBg = escapeHtml(settings.input_background_color || '#ffffff');
        var cols = Math.min(Math.max(parseInt(settings.columns, 10), 1), 4);

        return [
            ':host { display: block; }',
            '*, *::before, *::after { box-sizing: border-box; }',
            '',
            '.gymapp-widget {',
            '  font-family: ' + escapeHtml(settings.font_family) + ';',
            '  background: ' + escapeHtml(settings.background_color) + ';',
            '  color: ' + escapeHtml(settings.text_color) + ';',
            '  padding: ' + parseInt(settings.padding, 10) + 'px;',
            '  -webkit-font-smoothing: antialiased;',
            '  -moz-osx-font-smoothing: grayscale;',
            '  line-height: 1.5;',
            '}',
            '',
            '.gymapp-grid {',
            '  display: grid;',
            '  grid-template-columns: repeat(' + cols + ', 1fr);',
            '  gap: 20px;',
            '}',
            '@media (max-width: 640px) {',
            '  .gymapp-grid { grid-template-columns: 1fr; }',
            '}',
            '@media (min-width: 641px) and (max-width: 1024px) {',
            '  .gymapp-grid { grid-template-columns: repeat(' + Math.min(cols, 2) + ', 1fr); }',
            '}',
            '',
            '.gymapp-card {',
            '  border: 1px solid ' + escapeHtml(settings.card_border_color) + ';',
            '  border-radius: ' + cardR + 'px;',
            '  padding: 28px;',
            '  display: flex;',
            '  flex-direction: column;',
            '  background: ' + escapeHtml(settings.background_color) + ';',
            '  box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.03);',
            '  transition: border-color 0.2s ease;',
            '  animation: gymapp-fadeInUp 0.4s ease both;',
            '  position: relative;',
            '}',
            '.gymapp-card:hover {',
            '  border-color: rgba(' + pRgb + ', 0.3);',
            '}',
            '',
            '.gymapp-plan-name {',
            '  font-size: 1.125rem;',
            '  font-weight: 600;',
            '  margin: 0 0 6px;',
            '  color: ' + escapeHtml(settings.text_color) + ';',
            '  letter-spacing: -0.01em;',
            '}',
            '',
            '.gymapp-plan-description {',
            '  font-size: 0.8125rem;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  margin: 0 0 20px;',
            '  line-height: 1.6;',
            '}',
            '',
            '.gymapp-price-section { margin-bottom: 20px; }',
            '.gymapp-price {',
            '  display: flex;',
            '  align-items: flex-start;',
            '  font-size: 2.5rem;',
            '  font-weight: 700;',
            '  color: ' + escapeHtml(settings.text_color) + ';',
            '  margin: 0;',
            '  letter-spacing: -0.03em;',
            '  line-height: 1;',
            '}',
            '.gymapp-price-currency {',
            '  font-size: 1.125rem;',
            '  font-weight: 600;',
            '  margin-top: 0.35em;',
            '  margin-right: 2px;',
            '  letter-spacing: 0;',
            '}',
            '.gymapp-billing {',
            '  display: inline-block;',
            '  font-size: 0.6875rem;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  margin: 8px 0 0;',
            '  padding: 3px 10px;',
            '  background: rgba(' + sRgb + ', 0.07);',
            '  border-radius: 100px;',
            '  font-weight: 500;',
            '}',
            '',
            '.gymapp-divider {',
            '  height: 1px;',
            '  background: ' + escapeHtml(settings.card_border_color) + ';',
            '  margin: 0 0 20px;',
            '}',
            '',
            '.gymapp-features {',
            '  list-style: none;',
            '  padding: 0;',
            '  margin: 0 0 24px;',
            '  flex-grow: 1;',
            '}',
            '.gymapp-features li {',
            '  padding: 5px 0;',
            '  font-size: 0.8125rem;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  display: flex;',
            '  align-items: center;',
            '  gap: 10px;',
            '}',
            '.gymapp-features li svg { flex-shrink: 0; }',
            '',
            '.gymapp-btn {',
            '  display: inline-block;',
            '  width: 100%;',
            '  padding: 13px 24px;',
            '  background: ' + escapeHtml(settings.primary_color) + ';',
            '  color: ' + escapeHtml(settings.button_text_color) + ';',
            '  text-align: center;',
            '  text-decoration: none;',
            '  font-weight: 600;',
            '  font-size: 0.875rem;',
            '  border-radius: ' + btnR + 'px;',
            '  border: none;',
            '  cursor: pointer;',
            '  transition: all 0.15s ease;',
            '  box-shadow: 0 1px 3px rgba(' + pRgb + ', 0.2);',
            '  letter-spacing: 0.01em;',
            '  font-family: inherit;',
            '  line-height: 1.5;',
            '  margin-top: auto;',
            '}',
            '.gymapp-btn:hover {',
            '  filter: brightness(1.08);',
            '  transform: translateY(-1px);',
            '  box-shadow: 0 4px 12px rgba(' + pRgb + ', 0.3);',
            '}',
            '.gymapp-btn:active {',
            '  filter: brightness(0.95);',
            '  transform: translateY(0);',
            '  box-shadow: 0 1px 2px rgba(' + pRgb + ', 0.15);',
            '}',
            '.gymapp-btn:disabled {',
            '  opacity: 0.5;',
            '  cursor: not-allowed;',
            '  filter: none;',
            '  transform: none;',
            '}',
            '.gymapp-btn-secondary {',
            '  background: transparent;',
            '  color: ' + escapeHtml(settings.primary_color) + ';',
            '  border: 1px solid ' + escapeHtml(settings.card_border_color) + ';',
            '  box-shadow: none;',
            '}',
            '.gymapp-btn-secondary:hover {',
            '  background: rgba(' + pRgb + ', 0.04);',
            '  filter: none;',
            '  transform: none;',
            '  box-shadow: none;',
            '  border-color: rgba(' + pRgb + ', 0.3);',
            '}',
            '.gymapp-btn-secondary:active {',
            '  background: rgba(' + pRgb + ', 0.08);',
            '  filter: none;',
            '  transform: none;',
            '}',
            '',
            '@keyframes gymapp-fadeInUp {',
            '  from { opacity: 0; transform: translateY(12px); }',
            '  to { opacity: 1; transform: translateY(0); }',
            '}',
            '@keyframes gymapp-spin { to { transform: rotate(360deg); } }',
            '@keyframes gymapp-checkBounce {',
            '  0% { transform: scale(0); opacity: 0; }',
            '  50% { transform: scale(1.15); }',
            '  100% { transform: scale(1); opacity: 1; }',
            '}',
            '',
            '.gymapp-loading {',
            '  display: flex;',
            '  justify-content: center;',
            '  align-items: center;',
            '  padding: 48px;',
            '}',
            '.gymapp-spinner {',
            '  width: 28px;',
            '  height: 28px;',
            '  border: 2.5px solid ' + escapeHtml(settings.card_border_color) + ';',
            '  border-top-color: ' + escapeHtml(settings.primary_color) + ';',
            '  border-radius: 50%;',
            '  animation: gymapp-spin 0.7s linear infinite;',
            '}',
            '',
            '.gymapp-error {',
            '  text-align: center;',
            '  padding: 32px;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  font-size: 0.875rem;',
            '}',
            '',
            '.gymapp-powered {',
            '  text-align: center;',
            '  margin-top: 24px;',
            '  padding-top: 16px;',
            '  font-size: 0.6875rem;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  opacity: 0.6;',
            '  letter-spacing: 0.02em;',
            '}',
            '.gymapp-powered a { color: inherit; text-decoration: none; font-weight: 500; }',
            '.gymapp-powered a:hover { text-decoration: underline; }',
            '',
            '.gymapp-checkout {',
            '  max-width: 440px;',
            '  margin: 0 auto;',
            '  animation: gymapp-fadeInUp 0.3s ease both;',
            '}',
            '.gymapp-checkout-header { margin-bottom: 24px; }',
            '.gymapp-back-link {',
            '  display: inline-flex;',
            '  align-items: center;',
            '  gap: 6px;',
            '  font-size: 0.8125rem;',
            '  color: ' + escapeHtml(settings.primary_color) + ';',
            '  cursor: pointer;',
            '  background: none;',
            '  border: none;',
            '  padding: 0;',
            '  margin-bottom: 4px;',
            '  font-family: inherit;',
            '  font-weight: 500;',
            '  transition: opacity 0.15s;',
            '}',
            '.gymapp-back-link:hover { opacity: 0.7; }',
            '.gymapp-checkout-title {',
            '  font-size: 1.25rem;',
            '  font-weight: 600;',
            '  margin: 8px 0 0;',
            '  color: ' + escapeHtml(settings.text_color) + ';',
            '  letter-spacing: -0.01em;',
            '}',
            '.gymapp-plan-summary {',
            '  border: 1px solid ' + escapeHtml(settings.card_border_color) + ';',
            '  border-radius: ' + cardR + 'px;',
            '  padding: 16px 20px;',
            '  margin-bottom: 28px;',
            '  background: rgba(' + pRgb + ', 0.03);',
            '}',
            '.gymapp-plan-summary-name {',
            '  font-weight: 600;',
            '  margin: 0 0 2px;',
            '  font-size: 0.9375rem;',
            '}',
            '.gymapp-plan-summary-price {',
            '  font-size: 0.8125rem;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  margin: 0;',
            '}',
            '',
            '.gymapp-form-group { margin-bottom: 18px; }',
            '.gymapp-label {',
            '  display: block;',
            '  font-size: 0.8125rem;',
            '  font-weight: 500;',
            '  margin-bottom: 6px;',
            '  color: ' + escapeHtml(settings.text_color) + ';',
            '}',
            '.gymapp-input {',
            '  width: 100%;',
            '  padding: 11px 14px;',
            '  border: 1px solid ' + inputBorder + ';',
            '  border-radius: ' + inputR + 'px;',
            '  font-size: 0.875rem;',
            '  font-family: inherit;',
            '  color: ' + escapeHtml(settings.text_color) + ';',
            '  background: ' + inputBg + ';',
            '  outline: none;',
            '  transition: border-color 0.15s ease, box-shadow 0.15s ease;',
            '  line-height: 1.5;',
            '}',
            '.gymapp-input::placeholder { color: rgba(' + sRgb + ', 0.5); }',
            '.gymapp-input:focus {',
            '  border-color: ' + escapeHtml(settings.primary_color) + ';',
            '  box-shadow: 0 0 0 3px rgba(' + pRgb + ', 0.1);',
            '}',
            '.gymapp-input-error { border-color: #ef4444; }',
            '.gymapp-input-error:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }',
            '.gymapp-field-error {',
            '  font-size: 0.75rem;',
            '  color: #ef4444;',
            '  margin-top: 6px;',
            '}',
            '',
            '#gymapp-payment-element {',
            '  margin-bottom: 24px;',
            '  min-height: 50px;',
            '}',
            '',
            '.gymapp-dev-banner {',
            '  background: #fef3c7;',
            '  color: #92400e;',
            '  text-align: center;',
            '  padding: 10px 16px;',
            '  font-size: 0.75rem;',
            '  border-radius: ' + cardR + 'px;',
            '  margin-bottom: 20px;',
            '  font-weight: 500;',
            '}',
            '',
            '.gymapp-processing {',
            '  text-align: center;',
            '  padding: 64px 16px;',
            '  animation: gymapp-fadeInUp 0.3s ease both;',
            '}',
            '.gymapp-processing p {',
            '  margin-top: 20px;',
            '  font-size: 0.875rem;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '}',
            '',
            '.gymapp-success {',
            '  text-align: center;',
            '  max-width: 440px;',
            '  margin: 0 auto;',
            '  padding: 16px 0;',
            '  animation: gymapp-fadeInUp 0.3s ease both;',
            '}',
            '.gymapp-success-icon {',
            '  width: 56px;',
            '  height: 56px;',
            '  border-radius: 50%;',
            '  background: ' + escapeHtml(settings.primary_color) + ';',
            '  color: ' + escapeHtml(settings.button_text_color) + ';',
            '  display: flex;',
            '  align-items: center;',
            '  justify-content: center;',
            '  margin: 0 auto 20px;',
            '  animation: gymapp-checkBounce 0.4s ease 0.1s both;',
            '}',
            '.gymapp-success h2 {',
            '  font-size: 1.375rem;',
            '  font-weight: 700;',
            '  margin: 0 0 6px;',
            '  letter-spacing: -0.01em;',
            '}',
            '.gymapp-success > p {',
            '  font-size: 0.8125rem;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  margin: 0 0 8px;',
            '  line-height: 1.6;',
            '}',
            '.gymapp-access-code-section { margin: 24px 0; }',
            '.gymapp-access-code-label {',
            '  font-size: 0.6875rem;',
            '  text-transform: uppercase;',
            '  letter-spacing: 0.08em;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  margin: 0 0 8px;',
            '  font-weight: 600;',
            '}',
            '.gymapp-access-code {',
            '  display: inline-block;',
            '  background: rgba(' + pRgb + ', 0.06);',
            '  border: 1px solid rgba(' + pRgb + ', 0.15);',
            '  padding: 12px 28px;',
            '  border-radius: ' + cardR + 'px;',
            '  font-size: 1.75rem;',
            '  font-weight: 700;',
            '  letter-spacing: 0.15em;',
            '  color: ' + escapeHtml(settings.text_color) + ';',
            '}',
            '.gymapp-success-details {',
            '  border: 1px solid ' + escapeHtml(settings.card_border_color) + ';',
            '  border-radius: ' + cardR + 'px;',
            '  padding: 16px 20px;',
            '  margin: 20px 0;',
            '  text-align: left;',
            '  font-size: 0.8125rem;',
            '}',
            '.gymapp-success-details dt {',
            '  font-weight: 600;',
            '  display: inline;',
            '}',
            '.gymapp-success-details dd {',
            '  display: inline;',
            '  margin: 0 0 0 4px;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '}',
            '.gymapp-success-details > div { padding: 5px 0; }',
            '',
            '.gymapp-cta-card {',
            '  border: 1px solid rgba(' + pRgb + ', 0.2);',
            '  border-radius: ' + cardR + 'px;',
            '  padding: 24px;',
            '  margin: 24px 0 0;',
            '  text-align: center;',
            '  background: rgba(' + pRgb + ', 0.03);',
            '}',
            '.gymapp-cta-card h3 {',
            '  font-size: 1.0625rem;',
            '  font-weight: 600;',
            '  margin: 0 0 6px;',
            '  color: ' + escapeHtml(settings.text_color) + ';',
            '}',
            '.gymapp-cta-card p {',
            '  font-size: 0.8125rem;',
            '  color: ' + escapeHtml(settings.secondary_text_color) + ';',
            '  margin: 0 0 16px;',
            '  line-height: 1.6;',
            '}',
            '.gymapp-btn-group { margin-top: 20px; }',
            '',
            '.gymapp-error-view {',
            '  text-align: center;',
            '  padding: 48px 16px;',
            '  max-width: 440px;',
            '  margin: 0 auto;',
            '  animation: gymapp-fadeInUp 0.3s ease both;',
            '}',
            '.gymapp-error-icon {',
            '  width: 48px;',
            '  height: 48px;',
            '  border-radius: 50%;',
            '  background: #fef2f2;',
            '  display: flex;',
            '  align-items: center;',
            '  justify-content: center;',
            '  margin: 0 auto 16px;',
            '}',
            '.gymapp-error-view p {',
            '  color: #dc2626;',
            '  margin: 0 0 20px;',
            '  font-size: 0.875rem;',
            '  line-height: 1.6;',
            '}',
            '.gymapp-error-actions {',
            '  display: flex;',
            '  gap: 12px;',
            '  justify-content: center;',
            '}',
        ].join('\n');
    }

    function formatBillingPeriod(period) {
        var labels = {
            weekly: 'per week',
            monthly: 'per month',
            quarterly: 'per quarter',
            yearly: 'per year',
        };
        return labels[period] || '';
    }

    function checkSvg(color) {
        return '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M12 5L6.5 10.5L4 8" stroke="' + escapeHtml(color) + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    function renderPlans(data) {
        var settings = data.settings;
        var plans = data.plans;

        if (!plans || plans.length === 0) {
            return '<div class="gymapp-error">No plans available at this time.</div>';
        }

        var html = '<div class="gymapp-grid">';

        for (var i = 0; i < plans.length; i++) {
            var plan = plans[i];
            var delay = (i * 0.06).toFixed(2);

            html += '<div class="gymapp-card" style="animation-delay:' + delay + 's">';
            html += '<p class="gymapp-plan-name">' + escapeHtml(plan.name) + '</p>';

            if (settings.show_description && plan.description) {
                html += '<p class="gymapp-plan-description">' + escapeHtml(plan.description) + '</p>';
            }

            html += '<div class="gymapp-price-section">';
            html += '<p class="gymapp-price"><span class="gymapp-price-currency">$</span>' + escapeHtml(plan.price_formatted) + '</p>';
            html += '<p class="gymapp-billing">' + escapeHtml(plan.plan_type === 'one_time' ? 'One-time payment' : formatBillingPeriod(plan.billing_period)) + '</p>';
            html += '</div>';

            if (settings.show_features && plan.features && plan.features.length > 0) {
                html += '<div class="gymapp-divider"></div>';
                html += '<ul class="gymapp-features">';
                for (var j = 0; j < plan.features.length; j++) {
                    html += '<li>' + checkSvg(settings.primary_color) + escapeHtml(plan.features[j]) + '</li>';
                }
                html += '</ul>';
            }

            html += '<button class="gymapp-btn" data-select-plan="' + plan.id + '">' + escapeHtml(settings.button_text || 'Sign Up') + '</button>';
            html += '</div>';
        }

        html += '</div>';
        html += '<div class="gymapp-powered">Powered by <a href="' + escapeHtml(BASE_URL) + '" target="_blank" rel="noopener">GymApp</a></div>';

        return html;
    }

    function renderCheckoutForm(plan, settings) {
        var billingLabel = plan.plan_type === 'one_time' ? 'One-time payment' : formatBillingPeriod(plan.billing_period);

        var html = '<div class="gymapp-checkout">';
        html += '<div class="gymapp-checkout-header">';
        html += '<button class="gymapp-back-link" data-back-plans>\u2190 Back to plans</button>';
        html += '<h2 class="gymapp-checkout-title">Complete your signup</h2>';
        html += '</div>';

        html += '<div class="gymapp-plan-summary">';
        html += '<p class="gymapp-plan-summary-name">' + escapeHtml(plan.name) + '</p>';
        html += '<p class="gymapp-plan-summary-price">$' + escapeHtml(plan.price_formatted) + ' ' + escapeHtml(billingLabel) + '</p>';
        html += '</div>';

        html += '<div class="gymapp-form-group">';
        html += '<label class="gymapp-label" for="gymapp-name">Full Name *</label>';
        html += '<input class="gymapp-input" type="text" id="gymapp-name" name="name" required autocomplete="name">';
        html += '</div>';

        html += '<div class="gymapp-form-group">';
        html += '<label class="gymapp-label" for="gymapp-email">Email *</label>';
        html += '<input class="gymapp-input" type="email" id="gymapp-email" name="email" required autocomplete="email">';
        html += '</div>';

        html += '<div class="gymapp-form-group">';
        html += '<label class="gymapp-label" for="gymapp-phone">Phone (optional)</label>';
        html += '<input class="gymapp-input" type="tel" id="gymapp-phone" name="phone" autocomplete="tel">';
        html += '</div>';

        html += '<button class="gymapp-btn" data-submit-contact>Continue to Payment \u2192</button>';
        html += '</div>';

        return html;
    }

    function renderPaymentForm(settings, devMode) {
        var html = '<div class="gymapp-checkout">';
        html += '<div class="gymapp-checkout-header">';
        html += '<button class="gymapp-back-link" data-back-checkout>\u2190 Back</button>';
        html += '<h2 class="gymapp-checkout-title">Payment details</h2>';
        html += '</div>';

        if (devMode) {
            html += '<div class="gymapp-dev-banner">Development Mode &mdash; No real payment will be charged</div>';
            html += '<button class="gymapp-btn" data-simulate-payment>Complete Test Payment</button>';
        } else {
            html += '<div id="gymapp-payment-element"></div>';
            html += '<button class="gymapp-btn" data-pay-now>Pay Now</button>';
        }

        html += '</div>';
        return html;
    }

    function renderProcessing() {
        return '<div class="gymapp-processing"><div class="gymapp-loading"><div class="gymapp-spinner"></div></div><p>Processing your payment\u2026</p></div>';
    }

    function renderSuccess(result, settings) {
        var m = result.membership;
        var p = result.plan;

        var billingLabel = p.plan_type === 'one_time' ? 'One-time payment' : formatBillingPeriod(p.billing_period);

        var html = '<div class="gymapp-success">';
        html += '<div class="gymapp-success-icon"><svg width="28" height="28" viewBox="0 0 28 28" fill="none"><path d="M20 10L12 18L8 14" stroke="' + escapeHtml(settings.button_text_color) + '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
        html += '<h2>' + escapeHtml(settings.success_heading || "You\u2019re all set!") + '</h2>';
        html += '<p>' + escapeHtml(settings.success_message || 'Your membership is now active.') + '</p>';

        if (settings.show_access_code !== false) {
            html += '<div class="gymapp-access-code-section">';
            html += '<p class="gymapp-access-code-label">Your access code</p>';
            html += '<div class="gymapp-access-code">' + escapeHtml(m.access_code) + '</div>';
            html += '</div>';
        }

        if (settings.show_success_details !== false) {
            html += '<div class="gymapp-success-details">';
            html += '<div><dt>Plan:</dt><dd>' + escapeHtml(p.name) + '</dd></div>';
            html += '<div><dt>Price:</dt><dd>$' + escapeHtml(p.price_formatted) + ' ' + escapeHtml(billingLabel) + '</dd></div>';
            html += '<div><dt>Starts:</dt><dd>' + escapeHtml(m.starts_at) + '</dd></div>';
            if (m.ends_at) {
                html += '<div><dt>Ends:</dt><dd>' + escapeHtml(m.ends_at) + '</dd></div>';
            }
            html += '</div>';
        }

        html += '<p>A confirmation has been sent to ' + escapeHtml(result.email) + '</p>';

        if (settings.show_cta_card !== false) {
            var registerUrl = BASE_URL + '/register?email=' + encodeURIComponent(result.email);
            html += '<div class="gymapp-cta-card">';
            html += '<h3>Create an Account</h3>';
            html += '<p>Manage your membership, view billing history, and update your details all in one place.</p>';
            html += '<a class="gymapp-btn" href="' + escapeHtml(registerUrl) + '" target="_blank" rel="noopener">Create Free Account</a>';
            html += '</div>';
        }

        html += '<div class="gymapp-btn-group">';
        html += '<button class="gymapp-btn gymapp-btn-secondary" data-back-plans>Browse Plans</button>';
        html += '</div>';
        html += '</div>';

        return html;
    }

    function renderError(message, canRetry) {
        var html = '<div class="gymapp-error-view">';
        html += '<div class="gymapp-error-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 9L9 15M9 9L15 15" stroke="#dc2626" stroke-width="2" stroke-linecap="round"/></svg></div>';
        html += '<p>' + escapeHtml(message) + '</p>';
        html += '<div class="gymapp-error-actions">';
        if (canRetry) {
            html += '<button class="gymapp-btn" data-back-checkout>Try Again</button>';
        }
        html += '<button class="gymapp-btn gymapp-btn-secondary" data-back-plans>Back to Plans</button>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    function loadStripeJs() {
        return new Promise(function (resolve, reject) {
            if (window.Stripe) {
                resolve(window.Stripe);
                return;
            }
            var script = document.createElement('script');
            script.src = 'https://js.stripe.com/v3/';
            script.onload = function () { resolve(window.Stripe); };
            script.onerror = function () { reject(new Error('Failed to load Stripe.js')); };
            document.head.appendChild(script);
        });
    }

    function initWidget(container) {
        var teamSlug = container.getAttribute('data-team');
        var gymSlug = container.getAttribute('data-gym');

        if (!teamSlug || !gymSlug) {
            container.textContent = 'Widget error: missing data-team or data-gym attribute.';
            return;
        }

        var shadow = container.attachShadow({ mode: 'open' });

        var wrapper = document.createElement('div');
        wrapper.className = 'gymapp-widget';
        shadow.appendChild(wrapper);

        var style = document.createElement('style');
        style.textContent = buildStyles({
            primary_color: '#2563eb',
            background_color: '#ffffff',
            text_color: '#111827',
            secondary_text_color: '#6b7280',
            font_family: 'system-ui, -apple-system, sans-serif',
            card_border_radius: 16,
            button_border_radius: 8,
            input_border_color: '#e5e7eb',
            input_background_color: '#ffffff',
            input_border_radius: 8,
            card_border_color: '#e5e7eb',
            button_text_color: '#ffffff',
            padding: 16,
            columns: 3,
        });
        shadow.insertBefore(style, wrapper);

        wrapper.innerHTML = '<div class="gymapp-loading"><div class="gymapp-spinner"></div></div>';

        var apiUrl = BASE_URL + '/widget/' + encodeURIComponent(teamSlug) + '/' + encodeURIComponent(gymSlug);

        // Widget state
        var widgetData = null;
        var selectedPlan = null;
        var contactInfo = {};
        var intentResult = null;
        var stripeInstance = null;
        var stripeElements = null;
        var paymentElement = null;

        function showView(viewName, extraData) {
            // Clean up Stripe element before replacing HTML
            if (paymentElement) {
                paymentElement.unmount();
                paymentElement = null;
            }

            switch (viewName) {
                case 'plans':
                    selectedPlan = null;
                    intentResult = null;
                    stripeElements = null;
                    wrapper.innerHTML = renderPlans(widgetData);
                    break;

                case 'checkout':
                    wrapper.innerHTML = renderCheckoutForm(selectedPlan, widgetData.settings);
                    // Restore contact info if going back
                    if (contactInfo.name) {
                        var nameInput = shadow.querySelector('#gymapp-name');
                        var emailInput = shadow.querySelector('#gymapp-email');
                        var phoneInput = shadow.querySelector('#gymapp-phone');
                        if (nameInput) nameInput.value = contactInfo.name;
                        if (emailInput) emailInput.value = contactInfo.email;
                        if (phoneInput) phoneInput.value = contactInfo.phone || '';
                    }
                    break;

                case 'payment':
                    wrapper.innerHTML = renderPaymentForm(widgetData.settings, intentResult && intentResult.devMode);
                    if (intentResult && !intentResult.devMode && intentResult.clientSecret) {
                        mountPaymentElement();
                    }
                    break;

                case 'processing':
                    wrapper.innerHTML = renderProcessing();
                    break;

                case 'success':
                    wrapper.innerHTML = renderSuccess(extraData, widgetData.settings);
                    break;

                case 'error':
                    wrapper.innerHTML = renderError(extraData.message, extraData.canRetry !== false);
                    break;
            }
        }

        function mountPaymentElement() {
            var container = shadow.querySelector('#gymapp-payment-element');
            if (!container || !stripeElements) return;

            paymentElement = stripeElements.create('payment');
            paymentElement.mount(container);
        }

        function getContactInfo() {
            var name = (shadow.querySelector('#gymapp-name') || {}).value || '';
            var email = (shadow.querySelector('#gymapp-email') || {}).value || '';
            var phone = (shadow.querySelector('#gymapp-phone') || {}).value || '';
            return { name: name.trim(), email: email.trim(), phone: phone.trim() };
        }

        function validateContact(info) {
            var errors = {};
            if (!info.name) errors.name = 'Name is required.';
            if (!info.email) errors.email = 'Email is required.';
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(info.email)) errors.email = 'Please enter a valid email.';
            return errors;
        }

        function showFieldErrors(errors) {
            // Clear previous errors
            var existing = shadow.querySelectorAll('.gymapp-field-error');
            for (var i = 0; i < existing.length; i++) existing[i].remove();
            var errorInputs = shadow.querySelectorAll('.gymapp-input-error');
            for (var i = 0; i < errorInputs.length; i++) errorInputs[i].classList.remove('gymapp-input-error');

            for (var field in errors) {
                var input = shadow.querySelector('#gymapp-' + field);
                if (input) {
                    input.classList.add('gymapp-input-error');
                    var errEl = document.createElement('div');
                    errEl.className = 'gymapp-field-error';
                    errEl.textContent = errors[field];
                    input.parentNode.appendChild(errEl);
                }
            }
        }

        function setButtonLoading(selector, loading) {
            var btn = shadow.querySelector(selector);
            if (!btn) return;
            btn.disabled = loading;
            if (loading) {
                btn._originalText = btn.textContent;
                btn.textContent = 'Please wait\u2026';
            } else if (btn._originalText) {
                btn.textContent = btn._originalText;
            }
        }

        function handleSelectPlan(planId) {
            for (var i = 0; i < widgetData.plans.length; i++) {
                if (widgetData.plans[i].id === parseInt(planId, 10)) {
                    selectedPlan = widgetData.plans[i];
                    break;
                }
            }
            if (selectedPlan) {
                showView('checkout');
            }
        }

        function handleSubmitContact() {
            var info = getContactInfo();
            var errors = validateContact(info);

            if (Object.keys(errors).length > 0) {
                showFieldErrors(errors);
                return;
            }

            contactInfo = info;
            setButtonLoading('[data-submit-contact]', true);

            var intentUrl = widgetData.checkout_intent_url.replace('__PLAN_ID__', selectedPlan.id);

            fetch(intentUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    name: info.name,
                    email: info.email,
                    phone: info.phone || null,
                }),
            })
                .then(function (response) {
                    if (!response.ok) {
                        return response.json().then(function (data) {
                            throw new Error(data.message || 'Failed to start checkout.');
                        });
                    }
                    return response.json();
                })
                .then(function (data) {
                    intentResult = data;

                    if (data.devMode) {
                        showView('payment');
                        return;
                    }

                    // Load Stripe.js and initialize elements
                    return loadStripeJs().then(function (Stripe) {
                        stripeInstance = Stripe(widgetData.stripe_key);
                        stripeElements = stripeInstance.elements({
                            clientSecret: data.clientSecret,
                        });
                        showView('payment');
                    });
                })
                .catch(function (err) {
                    setButtonLoading('[data-submit-contact]', false);
                    showView('error', { message: err.message || 'Something went wrong. Please try again.', canRetry: true });
                });
        }

        function handlePayNow() {
            if (!stripeInstance || !stripeElements) return;

            setButtonLoading('[data-pay-now]', true);

            var returnUrl = BASE_URL + '/' + encodeURIComponent(teamSlug) + '/' + encodeURIComponent(gymSlug) + '/checkout/success';

            var confirmParams = {
                return_url: returnUrl,
                payment_method_data: {
                    billing_details: {
                        name: contactInfo.name,
                        email: contactInfo.email,
                    },
                },
            };

            if (contactInfo.phone) {
                confirmParams.payment_method_data.billing_details.phone = contactInfo.phone;
            }

            showView('processing');

            stripeInstance.confirmPayment({
                elements: stripeElements,
                redirect: 'if_required',
                confirmParams: confirmParams,
            }).then(function (result) {
                if (result.error) {
                    showView('error', { message: result.error.message, canRetry: true });
                    return;
                }

                callConfirm();
            });
        }

        function handleSimulatePayment() {
            showView('processing');
            callConfirm();
        }

        function callConfirm() {
            var body = {
                membership_plan: selectedPlan.id,
                name: contactInfo.name,
                email: contactInfo.email,
                phone: contactInfo.phone || null,
            };

            if (intentResult.subscriptionId) body.subscription_id = intentResult.subscriptionId;
            if (intentResult.paymentIntentId) body.payment_intent_id = intentResult.paymentIntentId;

            fetch(widgetData.checkout_confirm_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(body),
            })
                .then(function (response) {
                    if (!response.ok) {
                        return response.json().then(function (data) {
                            throw new Error(data.message || 'Failed to confirm membership.');
                        });
                    }
                    return response.json();
                })
                .then(function (data) {
                    showView('success', data);
                })
                .catch(function (err) {
                    showView('error', { message: err.message || 'Something went wrong. Please try again.', canRetry: false });
                });
        }

        // Event delegation
        wrapper.addEventListener('click', function (e) {
            var target = e.target;

            if (target.hasAttribute('data-select-plan')) {
                e.preventDefault();
                handleSelectPlan(target.getAttribute('data-select-plan'));
                return;
            }

            if (target.hasAttribute('data-back-plans')) {
                e.preventDefault();
                showView('plans');
                return;
            }

            if (target.hasAttribute('data-back-checkout')) {
                e.preventDefault();
                showView('checkout');
                return;
            }

            if (target.hasAttribute('data-submit-contact')) {
                e.preventDefault();
                handleSubmitContact();
                return;
            }

            if (target.hasAttribute('data-pay-now')) {
                e.preventDefault();
                handlePayNow();
                return;
            }

            if (target.hasAttribute('data-simulate-payment')) {
                e.preventDefault();
                handleSimulatePayment();
                return;
            }
        });

        // Fetch widget data
        fetch(apiUrl)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to load widget data');
                }
                return response.json();
            })
            .then(function (data) {
                widgetData = data;
                style.textContent = buildStyles(data.settings);
                showView('plans');
            })
            .catch(function () {
                wrapper.innerHTML = '<div class="gymapp-error">Unable to load membership plans. Please try again later.</div>';
            });
    }

    function init() {
        var containers = document.querySelectorAll('[data-gymapp-widget]');
        for (var i = 0; i < containers.length; i++) {
            if (!containers[i]._gymappInit) {
                containers[i]._gymappInit = true;
                initWidget(containers[i]);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

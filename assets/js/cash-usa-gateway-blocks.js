(function (window) {
    if (!window.wc || !window.wc.wcBlocksRegistry || !window.wc.wcSettings || !window.wp || !window.wp.element) {
        return;
    }

    const { __ } = window.wp.i18n;
    const { createElement, Fragment } = window.wp.element;
    const { getSetting } = window.wc.wcSettings;
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

    const settings = getSetting('cash_usa_data', {});

    const label = settings.title || __('Cash (USA Mail)', 'wc-cash-usa');

    const Instructions = () => {
        if (!settings.checkoutMessage) {
            return null;
        }

        return createElement('div', {
            className: 'wc-cash-usa-blocks-instructions',
            dangerouslySetInnerHTML: { __html: settings.checkoutMessage },
        });
    };

    const Description = () => {
        if (!settings.description) {
            return null;
        }

        return createElement('div', {
            className: 'wc-cash-usa-blocks-description',
            dangerouslySetInnerHTML: { __html: settings.description },
        });
    };

    const PaymentMethodContent = () =>
        createElement(
            Fragment,
            null,
            createElement(Description),
            createElement(Instructions)
        );

    registerPaymentMethod({
        name: 'cash_usa',
        label,
        ariaLabel: label,
        content: createElement(PaymentMethodContent),
        edit: createElement(PaymentMethodContent),
        canMakePayment: () => true,
        supports: settings.supports || { features: ['products'] },
    });
})(window);

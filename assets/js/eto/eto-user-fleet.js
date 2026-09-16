
ETO.User.Fleet = function() {
    var etoFn = {};

    etoFn.config = {
        init: [],
        lang: ['user'],
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'fleet');
    };

    etoFn.getIncome = function(quote, formId, routeId, userCommision) {
        var income = 0;

        if (typeof quote[routeId].driver_income != 'undefined') {

                var driverIncome = quote[routeId].driver_income[0],
                    subtotal = quote[routeId].quote.subtotal,
                    excluded = 0;

                if (ETO.settings('driver_income_total', false) !== true) {
                    driverIncome.payment_charges = etoFn.getPaymentCharges(quote[routeId].quote.subtotalWithDiscount, formId, routeId);

                    if (parseInt(ETO.settings('driver_income.payment_charges', 1)) === 1) {
                        subtotal = subtotal + driverIncome.payment_charges
                    }
                    if (parseInt(ETO.settings('driver_income.additional_items', 0)) === 0) {
                        excluded = excluded + driverIncome.additional_items;
                    }
                    if (parseInt(ETO.settings('driver_income.child_seats', 0)) === 0) {
                        excluded = excluded + driverIncome.child_seats;
                    }
                    if (parseInt(ETO.settings('driver_income.discounts', 1)) === 1) {
                        excluded = excluded + driverIncome.discounts;
                    }
                    if (parseInt(ETO.settings('driver_income.meet_and_greet', 0)) === 0) {
                        excluded = excluded + driverIncome.meet_and_greet;
                    }
                    if (parseInt(ETO.settings('driver_income.parking_charges', 0)) === 0) {
                        excluded = excluded + driverIncome.parking_charges;
                    }

                    subtotal = subtotal - excluded;
                } else {
                    subtotal = quote[routeId].quote.subtotalWithDiscount;
                }

            income = (parseFloat(subtotal) * parseFloat(userCommision)) / 100;
        }

        return income;
    };

    etoFn.getPaymentCharges = function(total, formId, routeId) {
        var /*bookingDetails = ETO.Booking.Form.config.bookingDetails[formId],*/
            bookingIncomeCharges = ETO.Booking.Form.config.bookingIncomeCharges[formId],
            paymentCharges = 0;

        if(typeof bookingIncomeCharges == 'undefined') {
            var paymentType = {},
                paymentId = ETO.Form.config.form.values[formId].booking[routeId].paymentType[0].payment_method;

            if(typeof ETO.settings('paymentType', undefined) != 'undefined') {
                $.each(ETO.settings('paymentType.data', []), function (k, v) {
                    if (parseInt(paymentId) === parseInt(v.id)) {
                        paymentType = v;
                        return;
                    }
                });

                if (parseInt(paymentType.factor_type) === 0) { // flat
                    paymentCharges = paymentType.price;
                } else if (parseInt(paymentType.factor_type) === 1) { // percent
                    paymentCharges = (parseFloat(total) / 100) * paymentType.price;
                }
            }
        } else {
            // var percent
            //     = (parseFloat(total) * 100)
            //         / Math.round(parseFloat(bookingDetails.total - bookingIncomeCharges.payment_charges) * 100) / 100;
            //
            // paymentCharges = (bookingDetails.payment_charges * percent) / 100;
            paymentCharges = bookingIncomeCharges.payment_charges;
        }
        return paymentCharges;
    };

    return etoFn;
}();



ETO.User.Customer = function() {
    var etoFn = {};

    etoFn.config = {
        init: [],
        lang: ['user'],
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'customer');
    };

    return etoFn;
}();
